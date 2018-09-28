<?php namespace App\Http\Controllers\Wechat;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserRegistered;
use App\Http\Controllers\Controller;
use App\Models\LoginRecord;
use App\Models\User;
use App\Services\Registrar;
use Log;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use App\Models\UserOauth;
use Illuminate\Support\Facades\Session;

/**
 * @author: wanghui
 * @date: 2017/6/21 下午6:55
 * @email: hank.huiwang@gmail.com
 */

class WechatController extends Controller
{

    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve(Request $request)
    {
        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            switch ($message->MsgType) {
                case 'event':
                    //'收到事件消息';
                    switch ($message->Event) {
                        case 'subscribe':
                            return '欢迎关注 Inwehub!';
                            break;
                        default:
                            return '欢迎关注 Inwehub!';
                            break;
                    }
                    break;
                case 'text':
                    return '收到文字消息';
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                // ... 其它消息
                default:
                    return '欢迎关注 Inwehub!';
                    break;
            }
        });
        try {
            $return = $wechat->server->serve();
            return $return->send();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }


    public function oauth(Request $request,JWTAuth $JWTAuth){
        Log::info('oauth_request');
        $redirect = $request->get('redirect','');
        $rc_code = $request->get('rc_code','');
        Session::put("wechat_user_redirect",$redirect);
        Session::put("wechat_user_rccode",$rc_code);

        $userInfo = session('wechat_userinfo');
        if($userInfo && isset($userInfo['app_token'])){
            $token = $userInfo['app_token'];
            if (empty($userInfo['id'])) return '请稍后再试';
            try {
                if ($user = $JWTAuth->authenticate($token)){
                    //登陆事件通知
                    event(new UserLoggedIn($user,'微信'));
                } else {
                    $wechat = app('wechat');
                    return $wechat->oauth->scopes(['snsapi_userinfo'])
                        ->setRequest($request)
                        ->redirect();
                }
            } catch (JWTException $e) {
                if (!isset($wechat)) {
                    $wechat = app('wechat');
                }
                return $wechat->oauth->scopes(['snsapi_userinfo'])
                    ->setRequest($request)
                    ->redirect();
            }

            return redirect(config('wechat.oauth.callback_redirect_url').'?openid='.$userInfo['id'].'&token='.$token.'&redirect='.$redirect);
        }
        if (!isset($wechat)) {
            $wechat = app('wechat');
        }

        return $wechat->oauth->scopes(['snsapi_userinfo'])
            ->setRequest($request)
            ->redirect();
    }

    public function oauthCallback(Request $request,JWTAuth $JWTAuth){
        Log::info('oauth_callback',$request->all());
        $wechat = app('wechat');
        $oauth = $wechat->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $userInfo = $user->toArray();
        //Log::info('oauth_callback_userinfo',$userInfo);
        //微信公众号和微信app的openid不同，但是unionid相同
        $unionid = isset($userInfo['original']['unionid'])?$userInfo['original']['unionid']:'';
        $oauthDataUpdate = true;
        //判断用户是否已注册完成,如未完成,走注册流程
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
            ->where('openid',$userInfo['id'])->first();
        if (!$oauthData && $unionid) {
            $oauthAppData = UserOauth::where('unionid',$unionid)->where('user_id','>',0)->first();
            if ($oauthAppData) {
                //如果已经用app微信登陆过了
                $oauthData = UserOauth::create(
                    [
                        'auth_type'=>UserOauth::AUTH_TYPE_WEIXIN_GZH,
                        'user_id'=> $oauthAppData->user_id,
                        'openid'   => $userInfo['id'],
                        'nickname'=>$userInfo['nickname'],
                        'avatar'=>$userInfo['avatar'],
                        'access_token'=>$userInfo['token'],
                        'refresh_token'=>'',
                        'expires_in'=>3600,
                        'full_info'=>$userInfo['original'],
                        'unionid' => $unionid,
                        'scope'=>'snsapi_userinfo'
                    ]
                );
                $oauthDataUpdate = false;
            }
        }

        $token = '';
        $userInfo['app_token'] = $token;
        $redirect = session('wechat_user_redirect');
        $rc_code = session('wechat_user_rccode');
        $rc_uid = 0;
        $needCreateUser = false;
        if ($oauthData) {
            $user = User::find($oauthData->user_id);
            if ($oauthDataUpdate) {
                $oauthData->update(
                    [
                        'openid'   => $userInfo['id'],
                        'nickname'=>$userInfo['nickname'],
                        'avatar'=>$userInfo['avatar'],
                        'access_token'=>$userInfo['token'],
                        'refresh_token'=>'',
                        'unionid' => $unionid,
                        'expires_in'=>3600,
                        'status' => 1,
                        'full_info'=>json_encode($userInfo['original']),
                        'scope'=>'snsapi_userinfo'
                    ]
                );
            }
            if ($user){
                $token = $JWTAuth->fromUser($user);
                $userInfo['app_token'] = $token;
                //登陆事件通知
                event(new UserLoggedIn($user,'微信'));
                Session::put("wechat_userinfo",$userInfo);
            } else {
                $needCreateUser = true;
            }
        } elseif($userInfo['id']) {
            $oauthData = UserOauth::create(
                [
                    'auth_type'=>UserOauth::AUTH_TYPE_WEIXIN_GZH,
                    'user_id'=> 0,
                    'openid'   => $userInfo['id'],
                    'nickname'=>$userInfo['nickname'],
                    'avatar'=>$userInfo['avatar'],
                    'access_token'=>$userInfo['token'],
                    'refresh_token'=>'',
                    'expires_in'=>3600,
                    'full_info'=>$userInfo['original'],
                    'unionid' => $unionid,
                    'scope'=>'snsapi_userinfo'
                ]
            );
            $needCreateUser = true;
        } else {
            Log::info('微信认证失败',['userinfo'=>$userInfo,'request'=>$request->all()]);
            return redirect('/wechat/oauth');
        }

        if ($needCreateUser) {
            //注册用户
            if ($rc_code) {
                //邀请码
                $rcUser = User::where('rc_code',$rc_code)->first();
                if ($rcUser) {
                    $rc_uid = $rcUser->id;
                }
            }
            $registrar = new Registrar();
            $new_user = $registrar->create([
                'name' => $oauthData->nickname,
                'email' => null,
                'mobile' => null,
                'rc_uid' => $rc_uid,
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
            event(new UserRegistered($new_user,$oauthData->id,'微信公众号'));
            $token = $JWTAuth->fromUser($new_user);
            $userInfo['app_token'] = $token;
            Session::put("wechat_userinfo",$userInfo);
        }

        return redirect(config('wechat.oauth.callback_redirect_url').'?openid='.$userInfo['id'].'&token='.$token.'&redirect='.$redirect);
    }


}
