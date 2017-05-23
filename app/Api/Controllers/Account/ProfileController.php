<?php namespace App\Api\Controllers\Account;

use App\Exceptions\ApiException;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use App\Services\City\CityData;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;

use App\Http\Requests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{

    /*个人基本资料*/
    public function info(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();
        $info = [];
        $info['id'] = $user->id;
        $info['name'] = $user->name;
        $info['mobile'] = $user->mobile;
        $info['email'] = $user->email;
        $info['avatar_url'] = $user->getAvatarUrl();
        $info['gender'] = $user->gender;
        $info['birthday'] = $user->birthday;
        $info['province']['key'] = $user->province;
        $info['province']['name'] = CityData::getProvinceName($user->province);
        $info['city']['key'] = $user->city;
        $info['city']['name'] = CityData::getCityName($user->province,$user->city);

        $info['hometown_province']['key'] = $user->hometown_province;
        $info['hometown_province']['name'] = CityData::getProvinceName($user->hometown_province);
        $info['hometown_city']['key'] = $user->hometown_city;
        $info['hometown_city']['name'] = CityData::getCityName($user->hometown_province,$user->hometown_city);

        $info['company'] = $user->company;
        $info['title'] = $user->title;
        $info['description'] = $user->description;
        $info['status'] = $user->status;
        $info['address_detail'] = $user->address_detail;
        $info['industry_tags'] = array_column($user->industryTags(),'name');
        if(empty($info['industry_tags'])) $info['industry_tags'] = '';
        $info['tags'] = Tag::whereIn('id',$user->userTag()->pluck('tag_id'))->pluck('name');
        $info['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
        $info['account_info_complete_percent'] = $user->getInfoCompletePercent();
        $info['total_money'] = 0;
        $user_money = UserMoney::find($user->id);
        if($user_money){
            $info['total_money'] = $user_money->total_money;
        }

        $jobs = $user->jobs()->orderBy('begin_time','desc')->get();
        foreach($jobs as &$job){
            $job->industry_tags = '';
            $job->product_tags = '';

            $job->industry_tags = $job->tags()->where('category_id',9)->pluck('name')->toArray();
            $job->product_tags = $job->tags()->where('category_id',10)->pluck('name')->toArray();
        }

        $projects = $user->projects()->orderBy('begin_time','desc')->get();

        foreach($projects as &$project){
            $project->industry_tags = '';
            $project->product_tags = '';

            $project->industry_tags = $project->tags()->where('category_id',9)->pluck('name')->toArray();
            $project->product_tags = $project->tags()->where('category_id',10)->pluck('name')->toArray();
        }

        $data = [
            'info'   => $info,
            'jobs'   => $jobs,
            'projects' => $projects,
            'edus'   => $user->edus()->orderBy('begin_time','desc')->get(),
            'trains'  => $user->trains()->orderBy('get_time','desc')->get()
        ];

        return self::createJsonData(true,$data,ApiException::SUCCESS,'ok');
    }

    //修改用户资料
    public function update(Request $request){
        $validateRules = [
            'name' => 'max:128',
            'gender'    => 'max:128|in:0,1,2',
            'company'   => 'max:128',
            'province' => 'max:128',
            'city'     => 'max:128',
            'hometown_province' => 'max:128',
            'hometown_city'     => 'max:128',
            'address_detail'  => 'max:255',
            'email'            => 'max:128|email',
            'birthday'         => 'max:128',
            'title' => 'max:255'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        if($request->input('name') !== null){
            $user->name = $request->input('name');
        }

        if($request->input('email') !== null){
            $user->email = strtolower($request->input('email'));
        }

        if($request->input('gender') !== null){
            $user->gender = $request->input('gender');
        }

        if($request->input('birthday') !== null){
            $user->birthday = $request->input('birthday');
        }

        if($request->input('title') !== null){
            $user->title = $request->input('title');
        }

        if($request->input('company') !== null){
            $user->company = $request->input('company');
        }

        if($request->input('description') !== null){
            $user->description = $request->input('description');
        }

        if($request->input('province') !== null){
            $user->province = $request->input('province');
        }

        if($request->input('city') !== null){
            $user->city = $request->input('city');
        }

        if($request->input('hometown_province') !== null){
            $user->hometown_province = $request->input('hometown_province');
        }

        if($request->input('hometown_city') !== null){
            $user->hometown_city = $request->input('hometown_city');
        }

        if($request->input('address_detail') !== null){
            $user->address_detail = $request->input('address_detail');
        }

        $user->save();
        if($request->input('industry_tags') !== null){
            $industry_tags = $request->input('industry_tags');
            $tags = Tag::whereIn('name',$industry_tags)->get();
            UserTag::detachByField($user->id,'industries');
            UserTag::multiIncrement($user->id,$tags,'industries');
        }

        return self::createJsonData(true,['account_info_complete_percent'=>$user->getInfoCompletePercent()]);
    }

    /**
     * 修改用户头像
     * @param Request $request
     */
    public function postAvatar(Request $request)
    {
        $validateRules = [
            'user_avatar' => 'required|image',
        ];
        $this->validate($request,$validateRules);
        if($request->hasFile('user_avatar')){
            $user_id = $request->user()->id;
            $file = $request->file('user_avatar');
            $avatarDir = User::getAvatarDir($user_id);
            $extension = strtolower($file->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');

            if(in_array($extension, $extArray)){
                $request->user()->addMediaFromRequest('user_avatar')->setFileName(User::getAvatarFileName($user_id,'origin').'.'.$extension)->toMediaCollection('avatar');
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'头像上传失败');
            }
            return self::createJsonData(true,['user_avatar_url'=>$request->user()->getAvatarUrl(),'account_info_complete_percent'=>$request->user()->getInfoCompletePercent()]);
        }
        return self::createJsonData(false,[],ApiException::BAD_REQUEST,'头像上传失败');

    }

    /**
     * 修改用户密码
     * @param Request $request
     */
    public function updatePassword(Request $request)
    {
        $validateRules = [
            'old_password' => 'required|min:6|max:16',
            'password' => 'required|min:6|max:16',
            'password_confirmation'=>'same:password',
        ];
        $this->validate($request,$validateRules);

        $user = $request->user();

        if(Hash::check($request->input('old_password'),$user->password)){
            $user->password = Hash::make($request->input('password'));
            $user->save();
            Auth()->logout();
            return self::createJsonData(true,[],ApiException::SUCCESS,'密码修改成功,请重新登录');
        }

        return self::createJsonData(false,[],ApiException::USER_PASSWORD_ERROR,'原始密码错误');
    }

    public function moneyLog(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = $request->user()->moneyLogs();
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $logs = $query->orderBy('id','DESC')->paginate(10);
        $list = [];
        foreach($logs as $log){
            $title = '';
            switch($log->money_type){
                case MoneyLog::MONEY_TYPE_ANSWER:
                    $title = '专业回答收入';
                    break;
                case MoneyLog::MONEY_TYPE_ASK:
                    $title = '付费问答';
                    break;
                case MoneyLog::MONEY_TYPE_FEE:
                    $title = '手续费';
                    break;
                case MoneyLog::MONEY_TYPE_WITHDRAW:
                    $title = '提现';
                    break;
            }
            $list[] = [
                "id"=> $log->id,
                "user_id" => $log->user_id,
                "change_money" => $log->change_money,
                "io"=> $log->io,
                "title"=> $title,
                "status" => $log->status,
                "created_at" => (string)$log->created_at
            ];
        }

        return self::createJsonData(true,$list);
    }

    public function wallet(Request $request){
        $data = get_pay_config();
        /**
         * @var User
         */
        $user = $request->user();

        $data['total_money'] = 0;
        $data['pay_settlement_money'] = 0;

        $user_money = UserMoney::find($user->id);
        if($user_money){
            $data['total_money'] = $user_money->total_money;
            $data['pay_settlement_money'] = $user_money->settlement_money;
        }

        return self::createJsonData(true,$data);
    }

}
