<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewGroupMemberApply extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $member;

    protected $user_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, GroupMember $member)
    {
        $this->user_id = $user_id;
        $this->member = $member;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database',  PushChannel::class, WechatNoticeChannel::class, SlackChannel::class];
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
            'url'    => '/group/setting/'.$this->member->group_id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => $this->member->user->avatar,
            'title'  => $this->member->user->name.'申请入圈',
            'body'   => $this->member->group->name,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->member->user->name.'申请入圈',
            'body'  => $this->member->group->name,
            'payload' => ['object_type'=>'group_member_apply','object_id'=>$this->member->group_id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = 'A7QHGjCGHFdOBnf3mumQF6hIJwRMyOekE1Vum1AnmFY';
        if (config('app.env') != 'production') {
            $template_id = '9tnV5heaeQ-KNcBB9J8zSnSvYBs87coRgR_ZVUfgT4I';
        }
        return [
            'first'    => '申请加入圈子通知',
            'keyword1' => $this->member->user->name,
            'keyword2' => $this->member->group->name,
            'keyword3' => '',
            'remark'   => '点击前往处理',
            'template_id' => $template_id,
            'target_url' => config('app.mobile_url').'#/group/setting/'.$this->member->group_id
        ];
    }

    public function toSlack($notifiable){
        return \Slack::to(config('slack.ask_activity_channel'))
            ->attach(
                [
                    'fields' => []
                ]
            )
            ->send('用户'.$this->member->user_id.'['.$this->member->user->name.']申请加入圈子['.$this->member->group->name.']');
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
