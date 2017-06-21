<?php namespace App\Http\Controllers\Wechat;
use App\Http\Controllers\Controller;
use App\Models\User;
use Log;
use Illuminate\Http\Request;
use App\Services\Registrar;
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
    public function serve()
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


    public function oauth(Request $request){
        Log::info('oauth_request');
        $user = session('wechat_user');
        // 未登录
        if (empty($user)) {
            $wechat = app('wechat');
            return $wechat->oauth->scopes(['snsapi_userinfo'])
                ->setRequest($request)
                ->redirect();
        } else {
            Log::info('已等录');
        }
    }

    public function oauthCallback(Request $request,Registrar $registrar){
        Log::info('oauth_callback',$request->all());
        $wechat = app('wechat');
        $oauth = $wechat->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $userInfo = $user->toArray();
        Session::put("wechat_user",$userInfo);

        //判断用户是否已注册完成,如未完成,走注册流程
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
            ->where('openid',$userInfo['id'])->first();

        if ($oauthData) {
            $user = User::find($oauthData->user_id);
            $oauthData->update(
                [
                    'openid'   => $userInfo['id'],
                    'nickname'=>$userInfo['nickname'],
                    'avatar'=>$userInfo['avatar'],
                    'access_token'=>$userInfo['token'],
                    'refresh_token'=>'',
                    'unionid' => isset($userInfo['original']['unionid'])?$userInfo['original']['unionid']:'',
                    'expires_in'=>3600,
                    'full_info'=>json_encode($userInfo['original']),
                    'scope'=>'snsapi_userinfo'
                ]
            );
        } else {
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
                    'scope'=>'snsapi_userinfo'
                ]
            );
        }


        Log::info('oauth_callback_data',[session('wechat_user')]);
    }


}
