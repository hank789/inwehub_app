<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Attention;
use App\Models\Notification as NotificationModel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewUserFollowing extends Notification implements ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $user_id;

    protected $attention;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Attention $attention)
    {
        $this->user_id = $user_id;
        $this->attention = $attention;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast', PushChannel::class, WechatNoticeChannel::class];
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

    protected function getTitle(){
        $user = User::find($this->attention->user_id);
        return '用户'.$user->name.'关注了你';
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
            'url'    => '/my/focus',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => config('image.user_default_avatar'),
            'title'  => $this->getTitle(),
            'body'   => '',
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $user = User::find($this->attention->user_id);
        $title = '用户'.$user->name.'关注了你';
        return [
            'title' => $title,
            'body'  => '',
            'payload' => ['object_type'=>'user_following','object_id'=>$user->uuid],
        ];
    }

    public function toWechatNotice($notifiable){
        return [
            'content' => '',
            'object_type'  => 'user_following',
            'object_id' => $this->attention->id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
