<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwakeUserQuestion extends Notification implements ShouldBroadcast,ShouldQueue
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
        return [PushChannel::class];
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

    public function toPush($notifiable)
    {
        $title = '悬赏问答邀请';
        switch ($this->question->question_type) {
            case 1:
                $object_type = 'pay_answer_awake';
                break;
            case 2:
                $object_type = 'free_answer_awake';
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
                $keyword1 = $this->question->title;
                $keyword2 = '付费咨询';
                $remark = '请立即前往确认回答';
                $first = '您好，有人向您付费咨询问题';
                $url = config('app.mobile_url').'#/answer/'.$this->question->id;
                break;
            case 2:
                $first = '悬赏问答邀请';
                $keyword1 = $this->question->title;
                $keyword2 = '悬赏问答';
                $remark = '悬赏金额'.$this->question->price.'元，点击前往参与回答';
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
            'first'    => $first,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'keyword3' => '',
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $url
        ];
    }
}
