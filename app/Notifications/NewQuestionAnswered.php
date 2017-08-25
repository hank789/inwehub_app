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

class NewQuestionAnswered extends Notification implements ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $question;
    protected $answer;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Question $question, Answer $answer)
    {
        $this->user_id = $user_id;
        $this->question = $question;
        $this->answer = $answer;
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
        return [
            'url'    => '/ask/'.$this->question->id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => $this->answer->user->avatar,
            'title'  => '专家'.$this->answer->user->name.'回复了你的问题',
            'body'   => $this->question->title,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => '您的提问专家已回答,请前往点评',
            'body'  => $this->question->title,
            'payload' => ['object_type'=>'question_answered','object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        return [
            'content' => $this->question->title,
            'object_type'  => 'question_answered',
            'object_id' => $this->answer->id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
