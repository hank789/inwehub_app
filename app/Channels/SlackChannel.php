<?php namespace App\Channels;
/**
 * @author: wanghui
 * @date: 2017/8/16 下午3:22
 * @email: hank.huiwang@gmail.com
 */
use App\Events\Frontend\System\Push;
use Illuminate\Notifications\Notification;

class SlackChannel {


    /**
     * 发送给定通知
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $notification->toSlack($notifiable);
    }

}