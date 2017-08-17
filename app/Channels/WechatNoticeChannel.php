<?php namespace App\Channels;
/**
 * @author: wanghui
 * @date: 2017/8/16 下午3:28
 * @email: wanghui@yonglibao.com
 */
use App\Logic\WechatNotice;
use App\Models\User;
use Illuminate\Notifications\Notification;

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
        WechatNotice::newTaskNotice($notifiable->id,$message['body'],$message['object_type'],$question);

    }

}