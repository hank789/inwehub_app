<?php namespace App\Http\Controllers\Wechat;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Http\Controllers\Controller;
use App\Models\LoginRecord;
use App\Models\User;
use Log;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;
use App\Models\UserOauth;
use Illuminate\Support\Facades\Session;

/**
 * @author: wanghui
 * @date: 2017/6/21 下午6:55
 * @email: wanghui@yonglibao.com
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
        Session::put("wechat_user_redirect",$redirect);
        $userInfo = session('wechat_userinfo');
        if($userInfo && isset($userInfo['app_token'])){
            $token = $userInfo['app_token'];
            try {
                if ($user = $JWTAuth->authenticate($token)){
                    //登陆事件通知
                    event(new UserLoggedIn($user,'微信'));
                    $this->saveLoginInfo($request,$user);
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
        //微信公众号和微信app的openid不同，但是unionid相同
        $unionid = isset($userInfo['original']['unionid'])?$userInfo['original']['unionid']:'';
        $oauthDataUpdate = true;
        //判断用户是否已注册完成,如未完成,走注册流程
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
            ->where('openid',$userInfo['id'])->first();
        if (!$oauthData && $unionid) {
            $oauthAppData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN)
                ->where('unionid',$unionid)->first();
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
                        'full_info'=>json_encode($userInfo['original']),
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
                $this->saveLoginInfo($request,$user);
            }
        } elseif($userInfo['id']) {
            UserOauth::create(
                [
                    'auth_type'=>UserOauth::AUTH_TYPE_WEIXIN_GZH,
                    'user_id'=> 0,
                    'openid'   => $userInfo['id'],
                    'nickname'=>$userInfo['nickname'],
                    'avatar'=>$userInfo['avatar'],
                    'access_token'=>$userInfo['token'],
                    'refresh_token'=>'',
                    'expires_in'=>3600,
                    'full_info'=>json_encode($userInfo['original']),
                    'unionid' => $unionid,
                    'scope'=>'snsapi_userinfo'
                ]
            );
        } else {
            Log::info('微信认证失败',['userinfo'=>$userInfo,'request'=>$request->all()]);
            return redirect('/wechat/oauth');
        }

        return redirect(config('wechat.oauth.callback_redirect_url').'?openid='.$userInfo['id'].'&token='.$token.'&redirect='.$redirect);
    }

    protected function saveLoginInfo(Request $request,User $user){
        $clientIp = $request->getClientIp();
        $loginrecord = new LoginRecord();
        $loginrecord->ip = $clientIp;

        $location = $this->findIp($clientIp);
        array_filter($location);
        $loginrecord->address = trim(implode(' ', $location));
        $loginrecord->device_system = $request->input('device_system');
        $loginrecord->device_name = $request->input('device_name');
        $loginrecord->device_model = $request->input('device_model');
        $loginrecord->device_code = $request->input('device_code');
        $loginrecord->user_id = $user->id;
        $loginrecord->address_detail = $request->input('current_address_name');
        $loginrecord->longitude = $request->input('current_address_longitude');
        $loginrecord->latitude = $request->input('current_address_latitude');
        $loginrecord->save();
    }


}
