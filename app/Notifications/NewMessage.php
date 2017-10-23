<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\IM\Message;
use App\Models\Notification as NotificationModel;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewMessage extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $message;
    protected $user_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Message $message)
    {
        $this->user_id = $user_id;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['broadcast', PushChannel::class, WechatNoticeChannel::class];
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
            'url'    => '',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_IM,
            'name'   => $this->message->user->name,
            'avatar' => $this->message->user->avatar,
            'body'   => $this->message->data,
            'created_at' => (string) $this->message->created_at
        ];
    }

    public function toPush($notifiable)
    {

        return [
            'title' => $this->message->owner->name.'回复了你',
            'body'  => $this->message->data['text'],
            'payload' => ['object_type'=>'im_message','object_id'=>$this->message->owner->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = 'LdZgOvnwDRJn9gEDu5UrLaurGLZfywfFkXsFelpKB94';
        if (config('app.env') != 'production') {
            $template_id = 'j4x5vAnKHcDrBcsoDooTHfWCOc_UaJFjFAyIKOpuM2k';
        }
        return [
            'first'    => '您好，'.$this->message->owner->name.'回复了您',
            'keyword1' => $this->message->owner->name,
            'keyword2' => $this->message->created_at,
            'keyword3' => $this->message->data['text'],
            'remark'   => '请点击查看详情！',
            'template_id' => $template_id,
            'target_url' => ''
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
