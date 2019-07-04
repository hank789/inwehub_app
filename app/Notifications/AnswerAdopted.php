<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Logic\QuestionLogic;
use App\Models\Answer;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnswerAdopted extends Notification implements ShouldBroadcast,ShouldQueue
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
        return ['database',  PushChannel::class, WechatNoticeChannel::class, SlackChannel::class];
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
        $url = '/ask/offer/'.$this->answer->id;
        return [
            'url'    => $url,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => $this->question->hide?config('image.user_default_avatar'):$this->question->user->avatar,
            'title'  => ($this->question->hide?'匿名':$this->question->user->name).'采纳了你的回答',
            'body'   => $this->question->title,
            'extra_body' => '悬赏金额稍后将会结算给您'
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => ($this->question->hide?'匿名':$this->question->user->name).'采纳了你的回答',
            'body'  => $this->question->title,
            'payload' => ['object_type'=>'free_answer','object_id'=>$this->question->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = '3jVbJizJM9Mjlk5hjaoGCh2kvN6Qn7QD7-DttMDM74Q';
        if (config('app.env') != 'production') {
            $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
        }
        return [
            'first'    => ($this->question->hide?'匿名':$this->question->user->name).'采纳了你的回答',
            'keyword1' => $this->question->title,
            'keyword2' => ($this->question->hide?'匿名':$this->question->user->name),
            'keyword3' => $this->answer->getContentText(),
            'keyword4' => (string)$this->answer->created_at,
            'remark'   => '点击查看问题详情',
            'template_id' => $template_id,
            'target_url' => config('app.mobile_url').'#/ask/offer/'.$this->answer->id
        ];
    }

    public function toSlack($notifiable){
        $fields[] = [
            'title' => '回答内容',
            'value' => $this->answer->getContentText()
        ];
        QuestionLogic::slackMsg('用户'.formatSlackUser($this->question->user).'采纳了'.formatSlackUser($this->answer->user).'的回答',$this->question,$fields);
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
