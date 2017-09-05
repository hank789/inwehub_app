<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\PushNotice as PushNoticeModel;

class PushNotice extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $pushNotice;
    protected $uid;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PushNoticeModel $pushNotice, $uid)
    {
        $this->pushNotice = $pushNotice;
        $this->uid = $uid;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [PushChannel::class];
    }

    public function toPush($notifiable)
    {
        $title = $this->pushNotice->title;
        $url = $this->pushNotice->url;
        switch ($this->pushNotice->notification_type){
            case PushNoticeModel::PUSH_NOTIFICATION_TYPE_READHUB:
                $object_type = 'push_notice_readhub';
                $parse_url = parse_url($url);
                $url = $parse_url['path'];
                break;
            default:
                $object_type = 'push_notice_article';
                break;
        }

        return [
            'title' => $title,
            'body'  => '点击前往查看',
            'payload' => ['object_type'=>$object_type,'object_id'=>$url],
        ];
    }
}
