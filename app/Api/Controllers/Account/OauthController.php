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
        $bindType = $request->input('bindType',1);
        $data = $request->all();
        \Log::info('oauth-callback',$data);
        $user = null;
        $token = null;
        $newUser = 0;
        $user_id = 0;
        try {
            $user = $JWTAuth->parseToken()->authenticate();
            $token = $JWTAuth->getToken();
            $user_id = $user->id;
            if ($bindType == 2) {
                //已经绑定过微信的不能再绑定其它微信了
                $oauthData = UserOauth::where('user_id',$user_id)->whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH])->first();
                if ($oauthData) {
                    //已绑定微信认证
                    return self::createJsonData(true,['token'=>$token, 'newUser'=>$newUser],ApiException::USER_WECHAT_ALREADY_BIND);
                }
            }
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        //微信公众号和微信app的openid不同，但是unionid相同
        $unionid = isset($data['full_info']['unionid'])?$data['full_info']['unionid']:'1';
        $oauthGzhData = UserOauth::where('unionid',$unionid)->whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH,UserOauth::AUTH_TYPE_WEAPP,UserOauth::AUTH_TYPE_WEAPP_ASK])->where('user_id','>',0)->first();
        if ($oauthGzhData) {
            if ($user->id <= 0) {
                //微信登陆
                $user_id = $oauthGzhData->user_id;
                $user = User::find($user_id);
                $token = $JWTAuth->fromUser($user);
            } elseif ($user->id > 0 && $oauthGzhData->user_id != $user->id) {
                if ($oauthGzhData->user->mobile) {
                    //微信认证已绑定其它手机号
                    return self::createJsonData(true,['token'=>'','newUser'=>$newUser, 'wechat_name'=>$oauthGzhData->nickname,'avatar'=>$oauthGzhData->user->avatar,'name'=>$oauthGzhData->user->name,'is_expert'=>$oauthGzhData->user->is_expert],ApiException::USER_OAUTH_BIND_OTHERS);
                } elseif ($bindType == 1){
                    return self::createJsonData(true,['token'=>'','newUser'=>$newUser, 'wechat_name'=>$oauthGzhData->nickname,'avatar'=>$oauthGzhData->user->avatar,'name'=>$oauthGzhData->user->name,'is_expert'=>$oauthGzhData->user->is_expert],ApiException::USER_WECHAT_EXIST_NOT_BIND_PHONE);
                } elseif ($bindType == 3) {
                    $oauthGzhData->user_id = $user->id;
                    $oauthGzhData->save();
                }
            } elseif ($user->id > 0 && $oauthGzhData->user_id == $user->id) {
                event(new UserLoggedIn($user,'App内微信'));
                return self::createJsonData(true,['token'=>$token, 'newUser'=>$newUser]);
            }
        }

        $object = UserOauth::where('auth_type',$type)->where('openid',$data['openid'])->first();
        //微信登陆
        if ($object) {
            if ($object->user_id && $user->id <= 0) {
                //微信登陆
                $user = User::find($object->user_id);
                $token = $JWTAuth->fromUser($user);
                $user_id = $user->id;
            } elseif ($user->id > 0 && $object->user_id>0 && $object->user_id != $user->id) {
                if ($object->user->mobile) {
                    //微信认证已绑定其它手机号
                    return self::createJsonData(true,['token'=>'','newUser'=>$newUser, 'wechat_name'=>$object->nickname,'avatar'=>$object->user->avatar,'name'=>$object->user->name,'is_expert'=>$object->user->is_expert],ApiException::USER_OAUTH_BIND_OTHERS);
                } elseif ($bindType == 1){
                    return self::createJsonData(true,['token'=>'','newUser'=>$newUser, 'wechat_name'=>$object->nickname,'avatar'=>$object->user->avatar,'name'=>$object->user->name,'is_expert'=>$object->user->is_expert],ApiException::USER_WECHAT_EXIST_NOT_BIND_PHONE);
                } elseif ($bindType == 3) {
                    $object->user_id = $user->id;
                    $object->save();
                }
            } elseif ($user->id > 0 && $object->user_id == $user->id) {
                event(new UserLoggedIn($user,'App内微信'));
                return self::createJsonData(true,['token'=>$token, 'newUser'=>$newUser]);
            }
            if($object->user_id && $object->user_id != $user->id && $bindType == 1){
                return self::createJsonData(true,['token'=>'','newUser'=>$newUser, 'wechat_name'=>$object->nickname,'avatar'=>$object->user->avatar,'name'=>$object->user->name,'is_expert'=>$object->user->is_expert],ApiException::USER_OAUTH_BIND_OTHERS);
            }
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
        if ($bindType == 3) {
            //导入微信头像和昵称
            if (str_contains($user->name,'手机用户')) {
                $user->name = $oauthData->nickname;
                $user->avatar = saveImgToCdn($oauthData->avatar);
                $user->save();
            }
        }
        if ($token && $user) {
            //登陆事件通知
            event(new UserLoggedIn($user,'App内微信'));
        }
        if ($bindType == 2 && $oauthData->user_id) {
            //合并账户
            $user->mergeUser($oauthData->user);
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
            $newUser = 1;
        }
        return self::createJsonData(true,['token'=>$token, 'newUser'=>$newUser]);
    }


}