<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Notification as NotificationModel;
use App\Models\Submission;
use App\Services\RateLimiter;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSubmission extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $submission;
    protected $user_id;
    protected $title;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Submission $submission)
    {
        $this->user_id = $user_id;
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        //自己发的不通知
        if ($this->user_id == $this->submission->user_id) return [];
        if ($this->submission->group_id) {
            $group = Group::find($this->submission->group_id);
            $groupMember = GroupMember::where('user_id',$this->user_id)->where('group_id',$group->id)->first();
            if (!$groupMember) return [];
            if (!$groupMember->is_notify) return [];
            $this->title = '圈子['.$group->name.']发布了新'.($this->submission->type == 'link' ? '文章':'分享');
        } else {
            $this->title = $this->submission->user->name.'发布了新'.($this->submission->type == 'link' ? '文章':'分享');
        }
        $limit = RateLimiter::instance()->increase('push_notify_user_submission',$this->user_id,60*60*24);
        if ($limit == RateLimiter::STATUS_BAD) return [];

        $via = ['database', 'broadcast'];
        if ($notifiable->checkCanDisturbNotify()){
            $via[] = PushChannel::class;
            //$via[] = WechatNoticeChannel::class;
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
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $body = strip_tags($this->submission->title);
        if ($this->submission->type == 'link') {
            $body = strip_tags($this->submission->data['title']);
        }
        return [
            'url'    => '/c/'.$this->submission->category_id.'/'.$this->submission->slug,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_READ,
            'avatar' => $this->submission->owner->avatar,
            'title'  => $this->title,
            'body'   => $body,
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        if ($this->submission->group_id) {
            $group = Group::find($this->submission->group_id);
            $title = $group->name;
        } else {
            $title = $this->title;
        }
        $body = strip_tags($this->submission->title);
        if ($this->submission->type == 'link') {
            $body = strip_tags($this->submission->data['title']);
        }
        if (empty($body)) return false;
        return [
            'title' => $title,
            'inAppTitle' => $this->title,
            'forcePush' => $this->submission->data['sourceViews']??false,
            'body'  => $body,
            'payload' => ['object_type'=>'readhub_new_submission','object_id'=>'/c/'.$this->submission->category_id.'/'.$this->submission->slug],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '您的企业申请已处理';
        $keyword2 = date('Y-m-d H:i',strtotime($this->company->created_at));
        $remark = '请点击查看详情！';
        $keyword3 = '';
        switch ($this->company->apply_status){
            case Company::APPLY_STATUS_SUCCESS:
                $keyword3 = '恭喜你成为平台认证企业！';
                break;
            case Company::APPLY_STATUS_REJECT:
                $keyword3 = '很抱歉，您的企业认证未通过审核';
                $remark = '点击前往重新申请！';
                break;
        }
        if (empty($keyword3)) return null;

        $target_url = config('app.mobile_url').'#/company/my';
        $template_id = '0trIXYvvZAsQdlGb9PyBIlmX1cfTVx4FRqf0oNPI9d4';
        if (config('app.env') != 'production') {
            $template_id = 'IOdf5wfUUoF1ojLAF2_rDAzfxtghfkQ0sJMgFpht_gY';
        }
        return [
            'first'    => $first,
            'keyword1' => '企业账户申请认证',
            'keyword2' => $keyword2,
            'keyword3' => $keyword3,
            'remark'   => $remark,
            'template_id' => $template_id,
            'target_url' => $target_url
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
