<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
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

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, CreditModel $creditLog)
    {
        $this->user_id = $user_id;
        $this->creditLog = $creditLog;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
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
        return [
            'url'    => '/my',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_INTEGRAL,
            'avatar' => '',
            'name'   => $this->creditLog->user->name,
            'title'  => $title,
            'body'   => $body,
            'current_coins' => $this->creditLog->current_coins + $this->creditLog->coins,
            'current_credits' => $this->creditLog->current_credits + $this->creditLog->credits,
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


    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
