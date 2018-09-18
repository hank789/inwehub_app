<?php namespace App\Channels;
/**
 * @author: wanghui
 * @date: 2017/8/16 下午3:22
 * @email: hank.huiwang@gmail.com
 */
use App\Events\Frontend\System\Push;
use App\Services\RateLimiter;
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
        if ($message) {
            RateLimiter::instance()->increase('push_notify_user_'.date('Ymd'),$notifiable->id,3600*24);
            // 将通知发送给 $notifiable 实例
            //3分钟内只接收一条推送
            if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('push_notify_user',$notifiable->id,180)) {
                event(new Push($notifiable->id,$message['title'],strip_tags($message['body']),$message['payload']));
            }
        }
    }

}