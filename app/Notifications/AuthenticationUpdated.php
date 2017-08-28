<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $title = $this->getTitle();
        return [
            'url'    => '/my',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => config('image.user_default_avatar'),
            'title'  => $title,
            'body'   => '',
            'extra_body' => ''
        ];
    }

    protected function getTitle(){
        $title = '';
        switch ($this->authentication->status){
            case 1:
                $title = '恭喜你成为平台认证专家！';
                break;
            case 4:
                $title = '很抱歉，您的专家认证未通过审核：'.$this->authentication->failed_reason;
                break;
        }
        return $title;
    }

    public function toPush($notifiable)
    {
        $title = '';
        $body = '点击前往查看';
        $object_type = '';
        switch ($this->authentication->status){
            case 1:
                $title = '恭喜你成为平台认证专家！';
                $object_type = 'authentication_success';
                break;
            case 4:
                $title = '很抱歉，您的专家认证未通过审核';
                $body  = $this->authentication->failed_reason;
                $object_type = 'authentication_fail';
                break;
        }

        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>$object_type,'object_id'=>$this->authentication->user_id],
        ];
    }

    public function toWechatNotice($notifiable){

        return [
            'content' => '平台专家身份认证',
            'object_type'  => 'authentication',
            'object_id' => $this->authentication->user_id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->authentication->user_id];
    }
}
