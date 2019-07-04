<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Groups\Group;
use App\Models\Notification as NotificationModel;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubmissionRecommend extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $submission;
    protected $user_id;
    protected $title = '您发布的文章被圈主设置成为了精选';

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
            $via[] = WechatNoticeChannel::class;
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'url'    => '/c/'.$this->submission->category_id.'/'.$this->submission->slug,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_READ,
            'avatar' => $this->submission->owner->avatar,
            'title'  => $this->title,
            'body'   => strip_tags($this->submission->title),
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->title,
            'body'  => strip_tags($this->submission->title),
            'payload' => ['object_type'=>'readhub_new_submission','object_id'=>'/c/'.$this->submission->category_id.'/'.$this->submission->slug],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您发布的文章被圈主设置成为了精选！';
        $target_url = config('app.mobile_url').'#/c/'.$this->submission->category_id.'/'.$this->submission->slug;
        $template_id = 'KjS-XKk-CKFjwskCpV_83zmidDWnBBB6w0YkgYr7YgE';
        if (config('app.env') != 'production') {
            $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
        }
        $user = User::find($this->user_id);
        return [
            'first'    => $first,
            'keyword1' => $user->name,
            'keyword2' => strip_tags($this->submission->title),
            'keyword3' => date('Y-m-d H:i',strtotime($this->submission->updated_at)),
            'remark'   => '点击查看详情！',
            'template_id' => $template_id,
            'target_url' => $target_url
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
