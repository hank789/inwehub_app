<?php

namespace App\Notifications;

use App\Channels\PushChannel;
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

class GroupMemberApplyResult extends Notification implements ShouldQueue,ShouldBroadcast
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
        switch ($this->member->audit_status) {
            case GroupMember::AUDIT_STATUS_REJECT:
                return '您的入圈申请未通过';
                break;
            case GroupMember::AUDIT_STATUS_SUCCESS:
                return '您的入圈申请已通过';
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
            'url'    => '/group/'.$this->member->group_id,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => config('image.user_default_avatar'),
            'title'  => $this->getTitle(),
            'body'   => $this->member->group->name,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->getTitle(),
            'body'  => $this->member->group->name,
            'payload' => ['object_type'=>'group_member_join','object_id'=>$this->member->group_id],
        ];
    }

    public function toWechatNotice($notifiable){
        $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
        if (config('app.env') != 'production') {
            $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
        }
        return [
            'first'    => '您的入圈申请已有受理结果',
            'keyword1' => $this->member->group->name,
            'keyword2' => date('Y-m-d H:i',strtotime($this->member->created_at)),
            'keyword3' => $this->getTitle(),
            'remark'   => '点击查看详情！',
            'template_id' => $template_id,
            'target_url' => config('app.mobile_url').'#/group/'.$this->member->group_id
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
