<?php namespace App\Api\Controllers\Wechat;
use App\Api\Controllers\Controller;
use Log;
use Illuminate\Http\Request;

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
        $wechat = app('wechat');
        return $wechat->oauth->scopes(['snsapi_userinfo'])
            ->setRequest($request)
            ->redirect();
    }

    public function oauthCallback(Request $request){
        Log::info('oauth_callback',$request->all());
        $wechat = app('wechat');
        $oauth = $wechat->oauth;
        // 获取 OAuth 授权结果用户信息
        $user = $oauth->user();
        $_SESSION['wechat_user'] = $user->toArray();
        $targetUrl = empty($_SESSION['target_url']) ? '/' : $_SESSION['target_url'];

        Log::info('oauth_callback_data',[$_SESSION['wechat_user'],$targetUrl]);
    }


}
