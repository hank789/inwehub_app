<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Answer;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class FollowedUserAsked extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $question;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Question $question)
    {
        $this->user_id = $user_id;
        $this->question = $question;
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
        switch ($this->question->question_type) {
            case 1:
                $url = '/ask/offer/answers/'.$this->question->id;
                break;
            case 2:
                $url = '/ask/offer/answers/'.$this->question->id;
                break;
        }
        return [
            'url'    => $url,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => $this->question->user->avatar,
            'title'  => '您关注的@'.$this->question->user->name.'有了新提问',
            'body'   => $this->question->title,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        switch ($this->question->question_type) {
            case 1:
                $object_type = 'pay_answer';
                return null;
                break;
            case 2:
                $object_type = 'free_answer';
                break;
        }
        return [
            'title' => '您关注的@'.$this->question->user->name.'有了新提问',
            'body'  => $this->question->title,
            'payload' => ['object_type'=>$object_type,'object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        switch ($this->question->question_type) {
            case 1:
                $keyword1 = $this->question->title;
                $keyword2 = '问答任务邀请';
                $remark = '请立即前往确认回答';
                $first = '您好，您有新的回答邀请';
                $url = config('app.mobile_url').'#/ask/offer/answers/'.$this->question->id;
                return null;
                break;
            case 2:
                $keyword2 = '问答';
                $remark = '请点击前往参与回答';
                $url = config('app.mobile_url').'#/ask/offer/answers/'.$this->question->id;
                break;
            default:
                return null;
        }
        $template_id = 'bVUSORjeArW08YvwDIgYgEAnjo49GmBuLPN9CPzIYrc';
        if (config('app.env') != 'production') {
            $template_id = 'EdchssuL5CWldA1eVfvtXHo737mqiH5dWLtUN7Ynwtg';
        }
        return [
            'first'    => '您关注的@'.$this->question->user->name.'有了新提问',
            'keyword1' => $this->question->title,
            'keyword2' => $keyword2,
            'keyword3' => '',
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $url
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
