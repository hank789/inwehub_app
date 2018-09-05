<?php namespace App\Channels;
/**
 * @author: wanghui
 * @date: 2017/8/16 下午3:28
 * @email: hank.huiwang@gmail.com
 */
use App\Models\UserOauth;
use App\Third\Weapp\WeApp;
use Illuminate\Notifications\Notification;
use App\Events\Frontend\Wechat\Notice;

class WeappNoticeChannel {


    /**
     * 发送给定通知
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWeappNotice($notifiable);
        // 将通知发送给 $notifiable 实例
        // 微信小程序通知
        if ($message) {
            $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEAPP)
                ->where('user_id',$notifiable->id)->orderBy('updated_at','desc')->first();
            if ($oauthData) {
                $wxxcx = new WeApp();
                $wxxcx->getTemplateMsg()->send($oauthData->openid,$message['template_id'],$message['form_id'],$message['data'],$message['page']);
            }
        }
    }

}