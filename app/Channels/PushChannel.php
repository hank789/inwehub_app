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
            if (!RateLimiter::instance()->getValue('push_notify_user_'.date('Ymd'),$notifiable->id)) {
                $expire = 3600*24;
            } else {
                $expire = 0;
            }
            //记录当天总的推送次数
            RateLimiter::instance()->increase('push_notify_user_'.date('Ymd'),$notifiable->id,$expire);

            //记录单一事件的推送频率
            $key = str_replace('\\','-',get_class($notification)).'_'.$notifiable->id;
            if (!RateLimiter::instance()->getValue('push_notify_user',$key)) {
                $expire2 = 300;
            } else {
                $expire2 = 0;
            }

            // 将通知发送给 $notifiable 实例
            //5分钟内只接收一条推送
            if (($message['forcePush']??false) || config('app.env') != 'production' || RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('push_notify_user',$key,$expire2)) {
                event(new Push($notifiable->id,$message['title'],strip_tags($message['body']),$message['payload'],$message['inAppTitle']??false));
                if (config('app.env') == 'production') {
                    $mp = \Mixpanel::getInstance(config('app.mixpanel_token'));
                    $mp->identify($notifiable->id);
                    $mp->track("inwehub:push:send",['app'=>'inwehub','user_id'=>$notifiable->id,'page_title'=>'发送推送','page'=>$message['payload']['object_id'],'page_name'=>$message['payload']['object_type']]);
                }
            }
        }
    }

}