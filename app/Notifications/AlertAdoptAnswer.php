<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Logic\QuestionLogic;
use App\Models\Question;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertAdoptAnswer extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $question;
    protected $user_id;
    protected $notifySlack;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Question $question, $notifySlack = true)
    {
        $this->user_id = $user_id;
        $this->question = $question;
        $this->notifySlack = $notifySlack;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [PushChannel::class, WechatNoticeChannel::class,SlackChannel::class];
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
        $title = '快来采纳最佳回答吧';
        return [
            'title' => $title,
            'body'  => $this->question->title,
            'payload' => ['object_type'=>'free_answer','object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '快来采纳最佳回答吧';
        $keyword1 = $this->question->title;
        $keyword2 = (string) $this->question->created_at;
        $remark = '请点击前往采纳最佳回答';
        $url = config('app.mobile_url').'#/askCommunity/interaction/answers/'.$this->question->id;
        $template_id = 'N_FF16jJUBq2iiER3rCK1oT-HwXyYoJ_oLJDPsP9uUM';
        if (config('app.env') != 'production') {
            $template_id = 'EdchssuL5CWldA1eVfvtXHo737mqiH5dWLtUN7Ynwtg';
        }
        return [
            'first'    => $first,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'keyword3' => $this->question->answers,
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $url
        ];
    }

    public function toSlack($notifiable){
        if ($this->notifySlack) {
            $user = User::find($this->user_id);
            QuestionLogic::slackMsg('通知用户'.$this->user_id.'['.$user->name.']采纳最佳回答',$this->question);
        }
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
