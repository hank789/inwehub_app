<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Notification as NotificationModel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewInviteUserRegister extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $user_id;

    protected $register_uid;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, $register_uid)
    {
        $this->user_id = $user_id;
        $this->register_uid = $register_uid;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database',  PushChannel::class, WechatNoticeChannel::class];
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
        $user = User::find($this->register_uid);
        return [
            'url'    => '/invitation/friends',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => $user->avatar,
            'title'  => '您邀请的'.$user->name.'注册成功',
            'body'   => '',
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $user = User::find($this->register_uid);
        $title = '您邀请的'.$user->name.'注册成功';
        return [
            'title' => $title,
            'body'  => '点击前往查看',
            'payload' => ['object_type'=>'invite_user_register','object_id'=>$user->uuid],
        ];
    }

    public function toWechatNotice($notifiable){
        $user = User::find($this->register_uid);
        $first = '您邀请的'.$user->name.'注册成功';
        $keyword2 = date('Y-m-d H:i:s',strtotime($user->created_at));
        $remark = '点击查看详情';
        $template_id = '5Wqmbg6q6RGE5h_4cNqYjFLdrs3BjgyjoFmuelV9ZH0';
        if (config('app.env') != 'production') {
            $template_id = 'VWF9KdZYtqUKBO38XrtDz-srNxaKQA4QWUuEgeUg8UU';
        }
        $keyword1 = $user->name;
        $target_url = config('app.mobile_url').'#/invitation/friends';
        return [
            'first'    => $first,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'keyword3' => '',
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $target_url,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
