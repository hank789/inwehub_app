<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Article;
use App\Models\Collection;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivityEnroll extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $collection;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database',  PushChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $title = $this->getTitle();
        $article = Article::find($this->collection->source_id);
        return [
            'url'    => '/EnrollmentStatus/'.$this->collection->source_id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => config('image.user_default_avatar'),
            'title'  => $title,
            'body'   => $article->title,
            'extra_body' => ''
        ];
    }

    protected function getTitle(){
        $title = '';
        switch ($this->collection->status){
            case Collection::COLLECT_STATUS_VERIFY:
                $title = '恭喜你活动报名成功！';
                break;
            case Collection::COLLECT_STATUS_REJECT:
            case Collection::COLLECT_STATUS_NEED_RE_ENROLL:
                $title = '很抱歉，您的活动报名未通过审核';
                break;
        }
        return $title;
    }

    public function toPush($notifiable)
    {
        $title = '';
        switch ($this->collection->status){
            case Collection::COLLECT_STATUS_VERIFY:
                $title = '恭喜你活动报名成功！';
                $object_type = 'activity_enroll_success';
                break;
            case Collection::COLLECT_STATUS_NEED_RE_ENROLL:
            case Collection::COLLECT_STATUS_REJECT:
                $title = '很抱歉，您的活动报名未通过审核';
                $object_type = 'activity_enroll_fail';
                break;
        }

        return [
            'title' => $title,
            'body'  => '点击前往查看',
            'payload' => ['object_type'=>$object_type,'object_id'=>$this->collection->source_id],
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->collection->user_id];
    }
}
