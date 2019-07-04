<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Notification as NotificationModel;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class FollowedUserNewSubmission extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $submission;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Submission $submission)
    {
        $this->user_id = $user_id;
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_my_user_new_activity']??true)){
            $via[] = PushChannel::class;
        }
        return $via;
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

    public function getTitle() {
        $title = strip_tags($this->submission->title);
        if ($this->submission->type == 'link') {
            $title = strip_tags($this->submission->data['title']);
        }
        if (empty($title)) return false;
        return $title;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        if ($this->submission->type=='review') {
            $typeName = '点评';
            $url = '/dianping/comment/'.$this->submission->slug;
        } else {
            $typeName = '分享';
            $url = '/c/'.$this->submission->category_id.'/'.$this->submission->slug;
        }
        return [
            'url'    => $url,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_READ,
            'avatar' => $this->submission->owner->avatar,
            'title'  => '您关注的@'.$this->submission->owner->name.'发布了新'.$typeName,
            'body'   => $this->getTitle(),
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        if ($this->submission->type=='review') {
            $typeName = '点评';
            $url = '/dianping/comment/'.$this->submission->slug;
        } else {
            $typeName = '分享';
            $url = '/c/'.$this->submission->category_id.'/'.$this->submission->slug;
        }

        return [
            'title' => '您关注的@'.$this->submission->owner->name.'发布了新'.$typeName,
            'body'  => $this->getTitle(),
            'payload' => ['object_type'=>'readhub_new_submission','object_id'=>$url],
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
