<?php namespace App\Listeners;

/**
 * @author: wanghui
 * @date: 2017/8/16 下午4:09
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Events\NotificationSent;

class LogNotificationListener implements ShouldQueue {


    /**
     * 处理事件
     *
     * @param  NotificationSent  $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        // $event->channel
        // $event->notifiable
        // $event->notification
        switch ($event->channel) {
            case 'database':
                $notification = Notification::find($event->notification->id);
                if ($notification && isset($notification->data['notification_type'])){
                    if (isset($notification->data['created_at']) && $notification->data['created_at']){
                        $notification->created_at = $notification->data['created_at'];
                    }
                    $notification->notification_type = $notification->data['notification_type'];
                    $notification->save();
                }
                break;
        }
    }
}