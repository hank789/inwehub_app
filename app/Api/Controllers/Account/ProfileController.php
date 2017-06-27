<?php namespace App\Api\Controllers\Account;

use App\Cache\UserCache;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Logic\WithdrawLogic;
use App\Models\Answer;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\UserTag;
use App\Services\City\CityData;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
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
        $cache = UserCache::getUserInfoCache($user->id);
        if($cache){

        }

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
        $info['industry_tags'] = TagsLogic::formatTags($user->industryTags());
        if(empty($info['industry_tags'])) $info['industry_tags'] = '';
        $info['tags'] = TagsLogic::formatTags(Tag::whereIn('id',$user->userTag()->pluck('tag_id'))->get());
        $info['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
        $info['expert_level'] = $info['is_expert'] === 1 ? $user->authentication->getLevelName():'';
        $info['is_company'] = $user->userData->is_company;
        $info['show_my_wallet'] = $user->userMoney->total_money > 0 ? true:false;

        $info_percent = $user->getInfoCompletePercent(true);
        $info['account_info_complete_percent'] = $info_percent['score'];

        $infos = '';
        if($info_percent['unfilled']){
            $infos = '请完善'.User::getFieldHumanName($info_percent['unfilled'][0]).(count($info_percent['unfilled'])>1?'等':'').'信息';
        }


        $info['account_info_valid_percent'] = config('inwehub.user_info_valid_percent');
        $info['total_money'] = 0;
        $user_money = UserMoney::find($user->id);
        if($user_money){
            $info['total_money'] = $user_money->total_money;
        }
        $info['questions'] = $user->userData->questions;
        $info['answers'] = $user->userData->answers;
        //加上承诺待回答的
        $info['answers'] += Answer::where('user_id',$user->id)->where('status',3)->count();
        $info['tasks'] = $user->tasks->where('status',0)->count();
        $info['projects'] = $user->companyProjects->count();
        $info['user_level'] = $user->getUserLevel();
        $info['user_credits'] = $user->userData->credits;
        $info['user_coins'] = $user->userData->coins;

        $jobs = $user->jobs()->orderBy('begin_time','desc')->pluck('company');
        $job_desc = '';
        if($jobs->count()){
            $job_desc = $jobs[0].($jobs->count()>1?'等':'').$jobs->count().'个工作';
        }

        $projects = $user->projects()->orderBy('begin_time','desc')->pluck('project_name');
        $project_desc = '';
        if($projects->count()){
            $project_desc = $projects[0].($projects->count()>1?'等':'').$projects->count().'个项目';
        }

        $edus = $user->edus()->orderBy('begin_time','desc')->pluck('school');
        $edu_desc = '';
        if ($edus->count()) {
            $edu_desc = $edus[0].($edus->count()>1?'等':'').$edus->count().'所学校';
        }

        $trains = $user->trains()->orderBy('get_time','desc')->pluck('certificate');
        $train_desc = '';
        if ($trains->count()) {
            $train_desc = $trains[0].($trains->count()>1?'等':'').$trains->count().'个认证';
        }

        $data = [
            'info'   => $info,
            'infos'  => $infos,
            'jobs'   => $job_desc,
            'projects' => $project_desc,
            'edus'   => $edu_desc,
            'trains'  => $train_desc
        ];

        UserCache::setUserInfoCache($user->id,$data);

        return self::createJsonData(true,$data,ApiException::SUCCESS,'ok');
    }

    //修改用户资料
    public function update(Request $request){
        $validateRules = [
            'name' => 'max:128',
            'gender'    => 'nullable|in:0,1,2',
            'company'   => 'max:128',
            'province' => 'max:128',
            'city'     => 'max:128',
            'hometown_province' => 'max:128',
            'hometown_city'     => 'max:128',
            'address_detail'  => 'max:255',
            'email'            => 'nullable|email',
            'birthday'         => 'max:128',
            'title' => 'max:255'
        ];
        $user = $request->user();
        $validateRules['email'] = 'nullable|email|max:255|unique:users,email,'.$user->id;
        $this->validate($request,$validateRules);
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
            $tags = Tag::whereIn('id',$industry_tags)->get();
            UserTag::detachByField($user->id,'industries');
            UserTag::multiIncrement($user->id,$tags,'industries');
        }
        UserCache::delUserInfoCache($user->id);

        $percent = $user->getInfoCompletePercent();
        $this->creditAccountInfoCompletePercent($user->id,$percent);
        return self::createJsonData(true,['account_info_complete_percent'=>$percent]);
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
            $extension = strtolower($file->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');

            if(in_array($extension, $extArray)){
                $request->user()->addMediaFromRequest('user_avatar')->setFileName(User::getAvatarFileName($user_id,'origin').'.'.$extension)->toMediaCollection('avatar');
                $upload_count = Cache::increment('user_avatar_upload:'.$user_id);
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'头像上传失败');
            }
            UserCache::delUserInfoCache($user_id);
            if($upload_count == 1){
                //只有首次上传头像才加积分
                $this->credit($user_id,'upload_avatar');
            }
            $percent = $request->user()->getInfoCompletePercent();
            $this->creditAccountInfoCompletePercent($user_id,$percent);
            return self::createJsonData(true,['user_avatar_url'=>$request->user()->getAvatarUrl(),'account_info_complete_percent'=>$percent]);
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
        $data['is_bind_weixin'] = 0;
        $data['bind_weixin_nickname'] = '';
        $data['user_phone'] = secret_mobile($user->mobile);
        $user_oauth = UserOauth::where('user_id',$user->id)->where('auth_type','weixin')->first();
        if($user_oauth){
            $data['is_bind_weixin'] = 1;
            $data['bind_weixin_nickname'] = $user_oauth['nickname'];
        }

        $user_money = UserMoney::find($user->id);
        if($user_money){
            $data['total_money'] = $user_money->total_money;
            $data['pay_settlement_money'] = $user_money->settlement_money;
        }
        $data['withdraw_day_remain'] = $data['withdraw_day_limit'];
        $withdraw_used = WithdrawLogic::getUserWithdrawCount($user->id,'wx_transfer');
        if($withdraw_used){
            $data['withdraw_day_remain'] -= $withdraw_used;
        }

        return self::createJsonData(true,$data);
    }

    //上传简历
    public function uploadResume(Request $request){
        $validateRules = [
            'user_resume_1'  => 'required|image',
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;

        if(RateLimiter::instance()->increase('user:profile:upload:resume',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $count = $this->counter('user:resume:upload:'.date('Y-m-d').':'.$user_id,1);
        if ($count >= 2) {
            throw new ApiException(ApiException::USER_RESUME_UPLOAD_LIMIT);
        }

        for($i=1;$i<=5;$i++){
            $name = 'user_resume_'.$i;
            if($request->hasFile($name)){
                $file_0 = $request->file($name);
                $extension = strtolower($file_0->getClientOriginalExtension());
                $extArray = array('png', 'gif', 'jpeg', 'jpg');
                if(in_array($extension, $extArray)){
                    $request->user()->addMediaFromRequest($name)->setFileName(time().'_'.md5($file_0->getFilename()).'.'.$extension)->toMediaCollection('resume');
                }else{
                    return self::createJsonData(false,[],ApiException::BAD_REQUEST,'格式错误');
                }
            }
        }

        return self::createJsonData(true);
    }

}
