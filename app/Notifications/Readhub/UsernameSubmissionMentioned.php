<?php

namespace App\Notifications\Readhub;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Comment;
use App\Models\Submission;
use App\Models\User;
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
        $via = ['database', 'broadcast'];
        if ($notifiable->site_notifications['push_notify_mentions']??true){
            $via[] = PushChannel::class;
        }
        if ($notifiable->site_notifications['wechat_notify_mentions']??true){
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
        return [
            'url'    => '/c/'.$this->submission->category_id.'/'.$this->submission->slug,
            'name'   => $this->submission->owner->name,
            'avatar' => $this->submission->owner->avatar,
            'title'  => $this->submission->owner->name.'提到了你',
            'body'   => $this->submission->title,
            'submission_id' => $this->submission->id,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->submission->owner->username.'提到了你',
            'body'  => $this->submission->title,
            'payload' => [
                'object_type'=>'readhub_username_mentioned',
                'object_id'=>'/c/'.$this->submission->category_id.'/'.$this->submission->slug
            ],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您好，'.$this->submission->owner->name.'在回复中提到了你';
        $keyword2 = date('Y-m-d H:i:s',strtotime($this->submission->created_at));
        $keyword3 = $this->submission->title;
        $remark = '请点击查看详情！';
        $template_id = 'H_uaNukeGPdLCXPSBIFLCFLo7J2UBDZxDkVmcc1in9A';
        if (config('app.env') != 'production') {
            $template_id = '_kZK_NLs1GOAqlBfpp0c2eG3csMtAo0_CQT3bmqmDfQ';
        }
        $target_url = config('app.mobile_url').'#/c/'.$this->submission->category_id.'/'.$this->submission->slug;
        return [
            'first'    => $first,
            'keyword1' => $this->submission->owner->name,
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
