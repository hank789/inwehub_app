<?php namespace App\Api\Controllers\Wechat;
use App\Api\Controllers\Controller;
use Log;

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
        Log::info('request arrived.');

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            return "欢迎关注 Inwehub！";
        });

        $return = $wechat->server->serve();
        return $return->send();
    }
}
