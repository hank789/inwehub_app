<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserRegistered;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Models\UserOauth;
use App\Services\Registrar;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class OauthController extends Controller
{

    public function callback($type,Request $request,JWTAuth $JWTAuth){

        $validateRules = [
            'openid' => 'required',
            'nickname' => 'required',
            'avatar'   => 'required',
            'access_token' => 'required',
            'refresh_token' => 'required',
            'expires_in' => 'required',
            'scope' => 'required',
        ];

        $this->validate($request,$validateRules);

        $data = $request->all();
        $user = null;
        $token = null;
        $user_id = 0;
        try {
            $user = $JWTAuth->parseToken()->authenticate();
            $token = $JWTAuth->getToken();
            $user_id = $user->id;
        } catch (\Exception $e) {

        }
        //微信公众号和微信app的openid不同，但是unionid相同
        $unionid = isset($data['full_info']['unionid'])?$data['full_info']['unionid']:'1';
        $oauthGzhData = UserOauth::where('unionid',$unionid)->where('user_id','>',0)->first();
        if ($oauthGzhData && $oauthGzhData->user_id) {
            $user_id = $oauthGzhData->user_id;
            $user = User::find($user_id);
            $token = $JWTAuth->fromUser($user);
        }

        $object = UserOauth::where('auth_type',$type)->where('openid',$data['openid'])->first();
        //微信登陆
        if ($object && $object->user_id && !$user) {
            $user = User::find($object->user_id);
            $token = $JWTAuth->fromUser($user);
            $user_id = $user->id;
        }

        if($object && $user && $object->user_id && $object->user_id != $user->id){
            throw new ApiException(ApiException::USER_OAUTH_BIND_OTHERS);
        }

        $oauthData = UserOauth::updateOrCreate([
            'auth_type'=>$type,
            'openid'   => $data['openid']
        ],[
            'auth_type'=>$type,
            'user_id'=> $user_id,
            'openid'   => $data['openid'],
            'unionid'  => $unionid,
            'nickname'=>$data['nickname'],
            'avatar'=>$data['avatar'],
            'access_token'=>$data['access_token'],
            'refresh_token'=>$data['refresh_token'],
            'expires_in'=>$data['expires_in'],
            'full_info'=>isset($data['full_info']) ? json_encode($data['full_info']):'',
            'scope'=>$data['scope']
        ]);
        if ($token && $user) {
            //登陆事件通知
            event(new UserLoggedIn($user,'App内微信'));
        }
        if (!$oauthData->user_id) {
            //注册用户
            $registrar = new Registrar();
            $new_user = $registrar->create([
                'name' => $oauthData->nickname,
                'email' => null,
                'mobile' => null,
                'rc_uid' => 0,
                'title'  => '',
                'company' => '',
                'gender' => $oauthData['full_info']['sex']??0,
                'password' => time(),
                'status' => 1,
                'visit_ip' => $request->getClientIp(),
                'source' => User::USER_SOURCE_WEIXIN_GZH,
            ]);
            $new_user->attachRole(2); //默认注册为普通用户角色
            $new_user->userData->email_status = 1;
            $new_user->userData->save();
            $new_user->avatar = $oauthData->avatar;
            $new_user->save();
            $oauthData->user_id = $new_user->id;
            $oauthData->save();
            //注册事件通知
            event(new UserRegistered($new_user,$oauthData->id,'微信APP'));
            $token = $JWTAuth->fromUser($new_user);
        }
        return self::createJsonData(true,['token'=>$token]);
    }


}