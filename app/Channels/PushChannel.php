<?php namespace App\Channels;
/**
 * @author: wanghui
 * @date: 2017/8/16 下午3:22
 * @email: wanghui@yonglibao.com
 */
use App\Events\Frontend\System\Push;
use Illuminate\Notifications\Notification;

class PushChannel {


    /**
     * 发送给定通知
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toPush($notifiable);
        // 将通知发送给 $notifiable 实例
        event(new Push($notifiable->id,$message['title'],$message['body'],$message['payload']));
    }

}