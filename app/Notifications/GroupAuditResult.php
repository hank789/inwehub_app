<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Groups\Group;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class GroupAuditResult extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $group;

    protected $user_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Group $group)
    {
        $this->user_id = $user_id;
        $this->group = $group;
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

    protected function getTitle(){
        switch ($this->group->audit_status) {
            case Group::AUDIT_STATUS_REJECT:
                return '您的圈子审核未通过';
                break;
            case Group::AUDIT_STATUS_SUCCESS:
                return '您的圈子审核已通过';
                break;
            case Group::AUDIT_STATUS_CLOSED:
                return '您的圈子已被管理员关闭';
                break;
        }
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
            'url'    => '/group/detail/'.$this->group->id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => config('image.notice_default_icon'),
            'title'  => $this->getTitle(),
            'body'   => $this->group->name,
            'extra_body' => $this->group->failed_reason
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->getTitle(),
            'body'  => $this->group->name,
            'payload' => ['object_type'=>'group_audit_result','object_id'=>$this->group->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
        if (config('app.env') != 'production') {
            $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
        }
        return [
            'first'    => '您的圈子申请已有受理结果',
            'keyword1' => $this->group->name,
            'keyword2' => date('Y-m-d H:i',strtotime($this->group->created_at)),
            'keyword3' => $this->getTitle(),
            'remark'   => $this->group->failed_reason ? $this->group->failed_reason:'点击查看详情！',
            'template_id' => $template_id,
            'target_url' => config('app.mobile_url').'#/group/detail/'.$this->group->id
        ];
    }

    public function toSlack($notifiable){
        return \Slack::to(config('slack.ask_activity_channel'))
            ->attach(
                [
                    'fields' => []
                ]
            )
            ->send('圈子['.$this->group->name.']:'.$this->getTitle());
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
