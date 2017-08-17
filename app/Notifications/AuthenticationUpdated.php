<?php

namespace App\Notifications;

use App\Models\Authentication;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class AuthenticationUpdated extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $authentication;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
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
        switch ($notifiable->authentication->status){
            case 1:
                $body = '您的专家认证已通过审核！';
                break;
            case 4:
                $body = '很抱歉，您的专家认证未通过审核：'.$notifiable->authentication->failed_reason;
                break;
        }
        return [
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'body' => $body,
            'url'  => '/my'
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->authentication->user_id];
    }
}
