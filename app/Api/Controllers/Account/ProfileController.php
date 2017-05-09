<?php namespace App\Api\Controllers\Account;

use App\Exceptions\ApiException;
use App\Models\Area;
use App\Models\EmailToken;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
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
        $info['province'] = $user->province;
        $info['city'] = $user->city;
        $info['company'] = $user->company;
        $info['title'] = $user->title;
        $info['description'] = $user->description;
        $info['status'] = $user->status;
        $info['address_detail'] = $user->address_detail;
        $info['industry_tags'] = array_column($user->industryTags(),'name');
        $info['tags'] = Tag::whereIn('id',$user->userTag()->pluck('tag_id'))->pluck('name');
        $info['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;

        $jobs = $user->jobs()->orderBy('begin_time','desc')->get();
        foreach($jobs as &$job){
            $job->industry_tags = '';
            $job->product_tags = '';
            $tags = $job->tags();
            $industry_tags = $tags->where('category_id',9)->pluck('name')->toArray();
            if($industry_tags){
                $job->industry_tags = implode(',',$industry_tags);
            }
            $product_tags = $tags->where('category_id',10)->pluck('name')->toArray();
            if($product_tags){
                $job->product_tags = implode(',',$product_tags);
            }
        }

        $projects = $user->projects()->orderBy('begin_time','desc')->get();

        foreach($projects as &$project){
            $project->industry_tags = '';
            $project->product_tags = '';
            $tags = $project->tags();
            $industry_tags = $tags->where('category_id',9)->pluck('name')->toArray();
            if($industry_tags){
                $project->industry_tags = implode(',',$industry_tags);
            }
            $product_tags = $tags->where('category_id',10)->pluck('name')->toArray();
            if($product_tags){
                $project->product_tags = implode(',',$product_tags);
            }
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
            'name' => 'required|max:128',
            'gender'    => 'max:128',
            'industry_tags'  => 'max:128',
            'company'   => 'max:128',
            'province' => 'max:128',
            'city'     => 'max:128',
            'address_detail'  => 'max:128',
            'email'            => 'max:128|email',
            'birthday'         => 'max:128|date_format:Y-m-d',
            'title' => 'sometimes|max:128',
            'description' => 'sometimes|max:9999',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $user->name = $request->input('name');
        $user->gender = $request->input('gender');
        $user->birthday = $request->input('birthday');
        $user->title = $request->input('title');
        $user->company = $request->input('company');

        $user->description = $request->input('description');
        $user->province = $request->input('province');
        $user->city = $request->input('city');
        $user->address_detail = $request->input('address_detail');
        $user->save();
        $industry_tags = $request->input('industry_tags');
        $tags = Tag::whereIn('name',explode(',',$industry_tags))->get();
        UserTag::multiIncrement($user->id,$tags,'industries');

        return self::createJsonData(true);
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
            return self::createJsonData(true,['user_avatar_url'=>$request->user()->getAvatarUrl()]);
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

}
