<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WeappNoticeChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Groups\Group;
use App\Models\IM\Message;
use App\Models\IM\Room;
use App\Models\Notification as NotificationModel;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Services\RateLimiter;
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
    public function __construct($user_id, Message $message,$room_id)
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
        if ((isset($notifiable->to_slack) && $notifiable->to_slack) || !isset($notifiable->to_slack)) {
            $via[] = SlackChannel::class;
        }
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_chatted']??true)){
            if ($this->room_id) {
                $room = Room::find($this->room_id);
                switch ($room->source_type) {
                    case Demand::class:
                        $via[] = WeappNoticeChannel::class;
                        return $via;
                        break;
                    case Group::class:
                        if ((isset($notifiable->to_push) && $notifiable->to_push) || !isset($notifiable->to_push)) {
                            $via[] = PushChannel::class;
                        }
                        return $via;
                        break;
                }
            }
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
            'message_id' => $this->message->id,
            'name'   => $this->message->user->name,
            'avatar' => $this->message->user->avatar,
            'uuid'   => $this->message->user->uuid,
            'user_id'=> $this->message->user->id,
            'room_id'=> $this->room_id,
            'body'   => $this->message->data,
            'created_at' => (string) $this->message->created_at
        ];
    }

    public function toPush($notifiable)
    {
        $title = $this->message->user->name.'回复了你';
        $object_id = $this->message->user->id;
        $object_type = 'im_message';
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            switch ($room->source_type) {
                case Group::class:
                    $group = Group::find($room->source_id);
                    $title = '圈子['.$group->name.']有新的回复';
                    $object_id = $room->id;
                    $object_type = 'im_group_message';
                    break;
            }
        }
        return [
            'title' => $title,
            'body'  => $this->message->data['text']?:'[图片]',
            'payload' => ['object_type'=>$object_type,'object_id'=>$object_id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = 'LdZgOvnwDRJn9gEDu5UrLaurGLZfywfFkXsFelpKB94';
        if (config('app.env') != 'production') {
            $template_id = 'j4x5vAnKHcDrBcsoDooTHfWCOc_UaJFjFAyIKOpuM2k';
        }
        $target_url = config('app.mobile_url').'#/chat/'.$this->message->user->id;
        $first = '您好，'.$this->message->user->name.'回复了您';
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            switch ($room->source_type) {
                case Group::class:
                    $target_url = config('app.mobile_url').'#/group/chat/'.$room->id;
                    $group = Group::find($room->source_id);
                    $first = '圈子['.$group->name.']有新的回复';
                    break;
            }
        }
        return [
            'first'    => $first,
            'keyword1' => $this->message->user->name,
            'keyword2' => (string) $this->message->created_at,
            'keyword3' => $this->message->data['text']?:'[图片]',
            'remark'   => '请点击查看详情！',
            'template_id' => $template_id,
            'target_url' => $target_url
        ];
    }

    public function toWeappNotice($notifiable) {
        $room = Room::find($this->room_id);
        $data = [];
        $form_id = '';
        switch ($room->source_type) {
            case Demand::class:
                $demand = Demand::find($room->source_id);
                $user = User::find($notifiable->id);
                $userOauth = $user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                $formIds = RateLimiter::instance()->sMembers('user_oauth_formId_'.$userOauth->id);
                if ($formIds) {
                    $form_id = $formIds[0];
                    RateLimiter::instance()->sRem('user_oauth_formId_'.$userOauth->id,$form_id);
                } else {
                    $formIds = RateLimiter::instance()->sMembers('user_formId_'.$notifiable->id);
                    if ($formIds) {
                        $form_id = $formIds[0];
                        RateLimiter::instance()->sRem('user_formId_'.$notifiable->id,$form_id);
                    }
                }
                $data = [
                    'keyword1' => [
                        'value'=>$demand->title,
                        'color'=>'#173177'
                        ],
                    'keyword2' => [
                        'value'=>$this->message->data['text']?:'[图片]',
                        'color'=>'#173177'
                    ],
                    'keyword3' => [
                        'value'=>$this->message->user->name,
                        'color'=>'#173177'
                    ],
                    'keyword4' => [
                        'value'=>(string) $this->message->created_at,
                        'color'=>'#173177'
                    ],
                ];
                $page = 'pages/chat/chat?id='.$room->id;
                break;
        }
        if (empty($data) || empty($form_id)) return null;
        $template_id = '_Kw_OpHk996L85oC1ij1mxS9kHoq0fKtcUdkjzbVL6g';
        if (config('app.env') != 'production') {
            $template_id = 'DzHFhepcKwsjfwhqI6UBofyZKCfyEJih2e7iuUOB_P0';
        }
        return [
            'data'     => $data,
            'template_id' => $template_id,
            'form_id' => $form_id,
            'page'    => $page
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
        $notify = '用户'.$this->message->user_id.'['.$this->message->user->name.']回复了用户'.$this->user_id.'['.$current_user->name.']';
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            switch ($room->source_type) {
                case Demand::class:
                    $demand = Demand::find($room->source_id);
                    $fields[] = [
                        'title' => '回复对象：找顾问助手',
                        'value' => $demand->title
                    ];
                    break;
                case Group::class:
                    $group = Group::find($room->source_id);
                    $fields[] = [
                        'title' => '回复对象：圈子',
                        'value' => $group->name
                    ];
                    $notify = '用户'.$this->message->user_id.'['.$this->message->user->name.']回复了圈子['.$group->name.']';
                    break;
            }
        }
        return \Slack::to(config('slack.user_chat_channel'))
            ->attach(
                [
                    'fields' => $fields
                ]
            )
            ->send($notify);
    }

    public function broadcastOn(){
        return new PrivateChannel('room.'.$this->room_id.'.user.'.$this->user_id);
    }
}
