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

class AnswerPromiseOvertime extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $answer;
    protected $user_id;
    protected $overtime;
    protected $question;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, $overtime, Answer $answer)
    {
        $this->user_id = $user_id;
        $this->question = $answer->question;
        $this->answer = $answer;
        $this->overtime = $overtime;
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
            'url'    => '/answer/'.$this->question->id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => config('image.notice_default_icon'),
            'title'  => '您的回答即将延误，请及时处理！',
            'body'   => $this->question->title,
            'extra_body' => '截止时间：'.$this->answer->promise_time
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => '距离您的承诺时间还有'.$this->overtime.'分钟',
            'body'  => $this->question->title,
            'payload' => ['object_type'=>'answer','object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您好，您有问答任务即将延期,请及时处理!';
        $keyword2 = date('Y-m-d H:i',strtotime($this->answer->promise_time));
        $remark = '可点击详情立即前往回答';
        $target_url = config('app.mobile_url').'#/answer/'.$this->question->id;
        $template_id = 'aOeQPpVu_aHC1xuJgMlZqkkI4j9mmqjLXn3SvX2b3hg';
        if (config('app.env') != 'production') {
            $template_id = 'zvZO6wKROVb3fWCE8TGIOtQ3y3k4527wD_Lsk6dyNnM';
        }
        return [
            'first'    => $first,
            'keyword1' => $this->question->title,
            'keyword2' => $keyword2,
            'keyword3' => '',
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $target_url
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
