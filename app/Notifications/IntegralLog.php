<?php

namespace App\Notifications;

use App\Channels\SlackChannel;
use App\Models\Credit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Models\Notification as NotificationModel;
use App\Models\Credit as CreditModel;

class IntegralLog extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $user_id;

    protected $creditLog;

    protected $notifySlack;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, CreditModel $creditLog,$notifySlack = true)
    {
        $this->user_id = $user_id;
        $this->creditLog = $creditLog;
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
        $via = ['database'];
        $notBroadcasts = [
            CreditModel::KEY_FIRST_USER_SIGN_DAILY,
            CreditModel::KEY_COMMUNITY_ASK_ANSWERED,
            CreditModel::KEY_INVITE_USER,
            CreditModel::KEY_PRO_OPPORTUNITY_COMMENTED,
            CreditModel::KEY_ANSWER_COMMENT,
            CreditModel::KEY_COMMUNITY_ANSWER_COMMENT,
            CreditModel::KEY_READHUB_SUBMISSION_COMMENT,
            CreditModel::KEY_RATE_ANSWER_GOOD,
            CreditModel::KEY_NEW_COMMENT,
            CreditModel::KEY_RATE_ANSWER_BAD,
            CreditModel::KEY_ANSWER_UPVOTE,
            CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE,
            CreditModel::KEY_READHUB_SUBMISSION_UPVOTE,
            CreditModel::KEY_PRO_OPPORTUNITY_SIGNED,
            CreditModel::KEY_READHUB_SUBMISSION_COLLECT,
            CreditModel::KEY_COMMUNITY_ANSWER_COLLECT,
            CreditModel::KEY_READHUB_SUBMISSION_SHARE,
            CreditModel::KEY_ANSWER_SHARE,
            CreditModel::KEY_COMMUNITY_ANSWER_SHARE,
            CreditModel::KEY_PAY_FOR_VIEW_ANSWER,
            CreditModel::KEY_COMMUNITY_ASK_FOLLOWED,
            CreditModel::KEY_COMMUNITY_ANSWER_INVITED,
            CreditModel::KEY_NEW_UPVOTE,
            CreditModel::KEY_READHUB_NEW_SUBMISSION
        ];
        if (!in_array($this->creditLog->action,$notBroadcasts)) {
            $via[] = 'broadcast';
        }
        if ($this->notifySlack) {
            $via[] = SlackChannel::class;
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
        $title = CreditModel::$creditSetting[$this->creditLog->action]['notice_user'];
        $body = get_credit_message($this->creditLog->credits,$this->creditLog->coins);

        $credits = $this->creditLog->current_credits + $this->creditLog->credits;
        $before_level =$notifiable->getUserLevel($this->creditLog->current_credits);
        $current_level = $notifiable->getUserLevel($credits);

        return [
            'url'    => '/my',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_INTEGRAL,
            'integral_action' => $this->creditLog->action,
            'source_id' => $this->creditLog->source_id,
            'add_coins' => $this->creditLog->coins,
            'add_credits' => $this->creditLog->credits,
            'avatar' => '',
            'name'   => $this->creditLog->user->name,
            'title'  => $title,
            'body'   => $body,
            'current_coins' => $this->creditLog->current_coins + $this->creditLog->coins,
            'current_credits' => $this->creditLog->current_credits + $this->creditLog->credits,
            'before_level' => $before_level,
            'current_level' => $current_level,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $credits = $this->creditLog->current_credits + $this->creditLog->credits;
        $current_level =$notifiable->getUserLevel($this->creditLog->current_credits);
        $next_level = $notifiable->getUserLevel($credits);
        if ($next_level>$current_level) {
            return [
                'title' => '恭喜您升级到L'.$next_level,
                'body'  => '',
                'payload' => ['object_type'=>'notification_level_up','object_id'=>$next_level],
            ];
        } else {
            return null;
        }
    }

    public function toSlack($notifiable){
        $fields = [];
        $fields[] = [
            'title' => '行为',
            'value' => $this->creditLog->action
        ];
        if ($this->creditLog->source_subject) {
            $fields[] = [
                'title' => '主题',
                'value' => $this->creditLog->source_subject
            ];
        }

        return \Slack::to(config('slack.auto_channel'))
            ->attach(
                [
                    'fields' => $fields
                ]
            )
            ->send('用户'.$this->creditLog->user_id.'['.$this->creditLog->user->name.']'.get_credit_message($this->creditLog->credits,$this->creditLog->coins));
    }


    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
