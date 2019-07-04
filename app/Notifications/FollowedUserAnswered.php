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

class FollowedUserAnswered extends Notification implements ShouldBroadcast,ShouldQueue
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
                $url = '/ask/offer/'.$this->answer->id;
                break;
            case 2:
                $url = '/ask/offer/'.$this->answer->id;
                break;
        }
        return [
            'url'    => $url,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_TASK,
            'avatar' => $this->answer->user->avatar,
            'title'  => '您关注的@'.$this->answer->user->name.'有了新回答',
            'body'   => $this->question->title,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        switch ($this->question->question_type) {
            case 1:
                $object_id = $this->question->id;
                $object_type = 'pay_question_answered_askCommunity';
                break;
            case 2:
                $object_id = $this->answer->id;
                $object_type = 'free_question_answered';
                break;
            default:
                return null;
        }
        return [
            'title' => '您关注的@'.$this->answer->user->name.'有了新回答',
            'body'  => $this->question->title,
            'payload' => ['object_type'=>$object_type,'object_id'=>$object_id],
        ];
    }

    public function toWechatNotice($notifiable){
        switch ($this->question->question_type) {
            case 1:
                $url = config('app.mobile_url').'#/ask/offer/'.$this->answer->id;
                break;
            case 2:
                $url = config('app.mobile_url').'#/ask/offer/'.$this->answer->id;
                break;
            default:
                return null;
        }
        if (empty($first)) return null;
        $keyword2 = $this->answer->user->name;
        $remark = '可点击查看回答内容并评论';

        $template_id = 'AvK_7zJ8OXAdg29iGPuyddHurGRjXFAQnEzk7zoYmCQ';
        if (config('app.env') != 'production') {
            $template_id = 'hT6MT7Xg3hsKaU0vP0gaWxFZT-DdMVsGnTFST9x_Qwc';
        }
        return [
            'first'    => '您关注的@'.$this->answer->user->name.'有了新回答',
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
