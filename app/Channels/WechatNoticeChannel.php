<?php namespace App\Channels;
/**
 * @author: wanghui
 * @date: 2017/8/16 下午3:28
 * @email: hank.huiwang@gmail.com
 */
use Illuminate\Notifications\Notification;
use App\Events\Frontend\Wechat\Notice;

class WechatNoticeChannel {


    /**
     * 发送给定通知
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWechatNotice($notifiable);
        // 将通知发送给 $notifiable 实例
        // 微信通知
        if ($message) {
            event(new Notice($notifiable->id,$message['first'],$message['keyword1'],$message['keyword2'],$message['keyword3'],$message['keyword4']??'',$message['remark'],$message['template_id'],$message['target_url'].'?inwe_source=wechatNotice'));
        }
    }

}