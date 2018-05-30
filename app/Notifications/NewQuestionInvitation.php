<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Jobs\Question\ConfirmOvertime;
use App\Logic\QuestionLogic;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
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
    protected $invitation_id;
    protected $notifySlack;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Question $question, $from_user_id = '',$invitation_id='',$notifySlack = true)
    {
        $this->user_id = $user_id;
        $this->question = $question;
        $this->from_user_id = $from_user_id;
        $this->invitation_id = $invitation_id;
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
        return ['database', 'broadcast', PushChannel::class, WechatNoticeChannel::class,SlackChannel::class];
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
        $title = '问答任务邀请';
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
                $title = '问答任务邀请';
                if ($this->question->hide) {
                    $avatar = config('image.user_default_avatar');
                }
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
                $keyword1 = $this->question->title;
                $keyword2 = '问答任务邀请';
                $remark = '请立即前往确认回答';
                $first = '您好，您有新的回答邀请';
                $url = config('app.mobile_url').'#/answer/'.$this->question->id;
                break;
            case 2:
                $from_user = User::find($this->from_user_id);
                $first = $from_user->name.'邀请您回答问题';
                $keyword1 = $this->question->title;
                $keyword2 = '问答邀请';
                $remark = '请点击前往参与回答';
                $url = config('app.mobile_url').'#/askCommunity/interaction/answers/'.$this->question->id;
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

    public function toSlack($notifiable){
        if ($this->notifySlack) {
            if ($this->from_user_id) {
                $from_user = User::find($this->from_user_id);
                $inviter = $from_user->id.'['.$from_user->name.']';
            }else {
                $inviter = '[系统]';
            }
            $user = User::find($this->user_id);
            QuestionLogic::slackMsg('用户'.$inviter.'邀请用户'.$this->user_id.'['.$user->name.']回答问题',$this->question);
        }
        if ($this->invitation_id && $this->question->question_type == 1) {
            //延时处理是否需要告警专家
            dispatch((new ConfirmOvertime($this->question->id,$this->invitation_id))->delay(Carbon::now()->addMinutes(Setting()->get('alert_minute_expert_unconfirm_question',10))));
        }
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
