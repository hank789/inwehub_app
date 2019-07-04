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

class NewQuestionConfirm extends Notification implements ShouldBroadcast,ShouldQueue
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
        return ['database',  PushChannel::class, WechatNoticeChannel::class];
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
            'title'  => '专家'.$this->answer->user->name.'响应了你的问题',
            'body'   => $this->question->title,
            'extra_body' => '截止时间：'.$this->answer->promise_time
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => '您的提问专家已响应,点击查看',
            'body'  => $this->question->title,
            'payload' => ['object_type'=>'question_answer_confirmed','object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = 'AvK_7zJ8OXAdg29iGPuyddHurGRjXFAQnEzk7zoYmCQ';
        if (config('app.env') != 'production') {
            $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
        }
        return [
            'first'    => '您好，已有专家响应了您的问答任务',
            'keyword1' => $this->question->title,
            'keyword2' => $this->answer->user->name,
            'keyword3' => '',
            'remark'   => '可点击详情查看处理进度',
            'template_id' => $template_id,
            'target_url' => config('app.mobile_url').'#/ask/'.$this->question->id
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
