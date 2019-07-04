<?php

namespace App\Notifications\Readhub;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Submission;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class UsernameSubmissionMentioned extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable;

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
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['database'];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_mentioned']??true)){
            $via[] = PushChannel::class;
            $via[] = WechatNoticeChannel::class;
        }
        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //        return (new MailMessage)
//                    ->line('The introduction to the notification.')
//                    ->action('Notification Action', url('/'))
//                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
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
            'name'   => $this->submission->owner->name,
            'avatar' => $this->submission->owner->avatar,
            'title'  => $this->submission->owner->name.'在'.$typeName.'中提到了你',
            'body'   => strip_tags($this->submission->title),
            'submission_id' => $this->submission->id,
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
            'title' => $this->submission->owner->name.'在'.$typeName.'中提到了你',
            'body'  => strip_tags($this->submission->title),
            'payload' => [
                'object_type'=>'readhub_username_mentioned',
                'object_id'=>$url
            ],
        ];
    }

    public function toWechatNotice($notifiable){
        if ($this->submission->type=='review') {
            $typeName = '点评';
            $url = '/dianping/comment/'.$this->submission->slug;
        } else {
            $typeName = '分享';
            $url = '/c/'.$this->submission->category_id.'/'.$this->submission->slug;
        }
        $first = '您好，'.$this->submission->owner->name.'在'.$typeName.'中提到了你';
        $keyword2 = date('Y-m-d H:i:s',strtotime($this->submission->created_at));
        $keyword3 = '';
        $remark = strip_tags($this->submission->title);
        $template_id = '8dthRe3ZODzHmVZj0120-XQ1P0CQVyaj-KTIZZUgrxw';
        if (config('app.env') != 'production') {
            $template_id = '_781d_63IgFjtv7FeyghCdVuYeRs9xZSfPLqhQdi-ZQ';
        }
        $target_url = config('app.mobile_url').'#'.$url;
        return [
            'first'    => $first,
            'keyword1' => $typeName,
            'keyword2' => $keyword2,
            'keyword3' => $keyword3,
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $target_url
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
