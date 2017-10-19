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
            $avatar = $from_user->avatar;
        } else {
            $avatar = $this->question->user->avatar;
        }
        switch ($this->question->question_type) {
            case 1:
                $url = '/answer/'.$this->question->id;
                break;
            case 2:
                $url = '/askCommunity/interaction/answers/'.$this->question->id;
                break;
        }
        return [
            'url'    => $url,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => $avatar,
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
        switch ($this->question->question_type) {
            case 1:
                $object_type = 'pay_answer';
                break;
            case 2:
                $object_type = 'free_answer';
                break;
        }

        return [
            'title' => $title,
            'body'  => $this->question->title,
            'payload' => ['object_type'=>$object_type,'object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        switch ($this->question->question_type) {
            case 1:
                $object_type = 'pay_question_invite_answer_confirming';
                $content = $this->question->title;
                break;
            case 2:
                $object_type = 'free_question_invite_answer_confirming';
                $from_user = User::find($this->from_user_id);
                $content = $from_user->name.'邀请您回答问题';
                break;
        }
        return [
            'keyword1' => $content,
            'object_type'  => $object_type,
            'object_id' => $this->question->id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
