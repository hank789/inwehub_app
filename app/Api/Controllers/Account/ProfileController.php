<?php namespace App\Api\Controllers\Account;

use App\Exceptions\ApiException;
use App\Models\Area;
use App\Models\EmailToken;
use App\Models\Tag;
use App\Models\User;
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
        $info['gender'] = trans_gender_name($user->gender);
        $info['birthday'] = $user->birthday;
        $info['province'] = $user->province;
        $info['city'] = $user->city;
        $info['company'] = $user->company;
        $info['description'] = $user->description;
        $info['status'] = $user->status;
        $info['tags'] = Tag::whereIn('id',$user->userTag()->pluck('tag_id'))->pluck('name');
        $data = [
            'info'   => $info,
            'jobs'   => $user->jobs(),
            'projects' => $user->projects(),
            'edus'   => $user->edus(),
            'trans'  => $user->trains()
        ];
        return self::createJsonData(true,$data,ApiException::SUCCESS,'ok');
    }

    //修改用户资料
    public function update(Request $request){
        $validateRules = [
            'real_name' => 'required|max:128',
            'gender'    => 'max:128',
            'industry'  => 'max:128',
            'company'   => 'max:128',
            'working_province' => 'max:128',
            'working_city'     => 'max:128',
            'working_address'  => 'max:128',
            'email'            => 'max:128',
            'birthday'         => 'max:128',
            'title' => 'sometimes|max:128',
            'self_description' => 'sometimes|max:9999',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $user->name = $request->input('real_name');
        $user->gender = $request->input('gender');
        $user->birthday = $request->input('birthday');
        $user->title = $request->input('title');
        $user->description = $request->input('self_description');
        $user->province = $request->input('working_province');
        $user->city = $request->input('working_city');
        $user->address_detail = $request->input('working_address');
        $user->industry_tag_id = $request->input('industry');
        $user->save();
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
