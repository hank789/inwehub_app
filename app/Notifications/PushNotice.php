<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Models\Readhub\Submission;
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
        $item = [];
        switch ($this->pushNotice->notification_type){
            case PushNoticeModel::PUSH_NOTIFICATION_TYPE_READHUB:
                $object_type = 'push_notice_readhub';
                $recommendation = Submission::where('id',$url)->where('recommend_status','>=',Submission::RECOMMEND_STATUS_PENDING)->first()->toArray();
                $item['title'] = $recommendation['title'];
                $item['img_url'] = $recommendation['data']['img']??'';
                $item['publish_at'] = date('Y/m/d H:i',strtotime($recommendation['created_at']));
                $item['upvotes'] = $recommendation['upvotes'];
                $item['view_url'] = $recommendation['data']['url'];
                $item['comment_url'] = '/c/'.($recommendation['category_id']).'/'.$recommendation['slug'];
                $item['id'] = $recommendation['id'];
                break;
            case PushNoticeModel::PUSH_NOTIFICATION_TYPE_APP_SELF:
                $object_type = 'push_notice_app_self';
                $parse_url = parse_url($url);
                $url = $parse_url['path'];
                if ($parse_url['path'] === '/' && isset($parse_url['fragment'])){
                    $url = $parse_url['fragment'];
                }
                break;
            default:
                $object_type = 'push_notice_article';
                break;
        }

        return [
            'title' => $title,
            'body'  => '点击前往查看',
            'payload' => ['object_type'=>$object_type,'object_id'=>$url, 'object' => $item],
        ];
    }
}
