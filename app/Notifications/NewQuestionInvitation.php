<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewQuestionInvitation extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $question;
    protected $user_id;
    protected $from_user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Question $question, $from_user_id = '')
    {
        $this->user_id = $user_id;
        $this->question = $question;
        $this->from_user_id = $from_user_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', PushChannel::class, WechatNoticeChannel::class];
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
        $title = '专业问答任务邀请';
        if ($this->from_user_id) {
            $from_user = User::find($this->from_user_id);
            $title = $from_user->name.'邀请您回答问题';
        }
        return [
            'url'    => '/answer/'.$this->question->id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => $this->question->user->avatar,
            'title'  => $title,
            'body'   => $this->question->title,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $title = '您有新的回答邀请';
        if ($this->from_user_id) {
            $from_user = User::find($this->from_user_id);
            $title = $from_user->name.'邀请您回答问题';
        }
        return [
            'title' => $title,
            'body'  => $this->question->title,
            'payload' => ['object_type'=>'answer','object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $title = '您有新的回答邀请';
        if ($this->from_user_id) {
            $from_user = User::find($this->from_user_id);
            $title = $from_user->name.'邀请您回答问题';
        }
        return [
            'content' => $title,
            'object_type'  => 'question_invite_answer_confirming',
            'object_id' => $this->question->id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
