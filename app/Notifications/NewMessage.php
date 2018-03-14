<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\IM\Message;
use App\Models\IM\Room;
use App\Models\Notification as NotificationModel;
use App\Models\User;
use App\Models\Weapp\Demand;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
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
    protected $room_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Message $message,$room_id = 0)
    {
        $this->user_id = $user_id;
        $this->message = $message;
        $this->room_id = $room_id;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = ['broadcast'];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_chatted']??true)){
            $via[] = PushChannel::class;
            $via[] = WechatNoticeChannel::class;
        }
        if ((isset($notifiable->to_slack) && $notifiable->to_slack) || !isset($notifiable->to_slack)) {
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
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
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
            'url'    => '/chat/'.$this->message->user->id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_IM,
            'name'   => $this->message->user->name,
            'avatar' => $this->message->user->avatar,
            'uuid'   => $this->message->user->uuid,
            'user_id'=> $this->message->user->id,
            'body'   => $this->message->data,
            'created_at' => (string) $this->message->created_at
        ];
    }

    public function toPush($notifiable)
    {

        return [
            'title' => $this->message->user->name.'回复了你',
            'body'  => $this->message->data['text']?:'[图片]',
            'payload' => ['object_type'=>'im_message','object_id'=>$this->message->user->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = 'LdZgOvnwDRJn9gEDu5UrLaurGLZfywfFkXsFelpKB94';
        if (config('app.env') != 'production') {
            $template_id = 'j4x5vAnKHcDrBcsoDooTHfWCOc_UaJFjFAyIKOpuM2k';
        }
        return [
            'first'    => '您好，'.$this->message->user->name.'回复了您',
            'keyword1' => $this->message->user->name,
            'keyword2' => (string) $this->message->created_at,
            'keyword3' => $this->message->data['text']?:'[图片]',
            'remark'   => '请点击查看详情！',
            'template_id' => $template_id,
            'target_url' => config('app.mobile_url').'#/chat/'.$this->message->user->id
        ];
    }

    public function toSlack($notifiable){
        $current_user = User::find($this->user_id);
        $fields = [];
        if (isset($this->message->data['text']) && $this->message->data['text']) {
            $fields[] = [
                'title' => '回复内容',
                'value' => $this->message->data['text']
            ];
        }
        if (isset($this->message->data['img']) && $this->message->data['img']) {
            $fields[] = [
                'title' => '回复图片',
                'value' => $this->message->data['img']
            ];
        }
        return \Slack::to(config('slack.user_chat_channel'))
            ->attach(
                [
                    'fields' => $fields
                ]
            )
            ->send('用户'.$this->message->user_id.'['.$this->message->user->name.']回复了用户'.$this->user_id.'['.$current_user->name.']');
    }

    public function broadcastOn(){
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            switch ($room->source_type) {
                case Demand::class:
                    return new PrivateChannel('demand.'.$room->source_id.'.user.'.$this->user_id);
                    break;
            }
        }
        return ['notification.user.'.$this->user_id];
    }
}
