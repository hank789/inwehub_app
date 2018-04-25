<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Groups\Group;
use App\Models\Notification as NotificationModel;
use App\Models\Submission;
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
        $group = Group::find($this->submission->group_id);
        if ($this->user_id == $group->user_id) {
            //通知圈主
            $this->title = '您的圈子['.$group->name.']有新的'.($this->submission->type == 'link' ? '文章':'分享').'发布';
        } elseif ($this->submission->user_id == $group->user_id){
            //圈主发布的文章
            $this->title = '圈主['.$group->name.']发布了新的'.($this->submission->type == 'link' ? '文章':'分享');
        } else {
            return [];
        }
        $via = ['database', 'broadcast'];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_my_user_new_activity']??true)){
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
        return [
            'url'    => '/c/'.$this->submission->category_id.'/'.$this->submission->slug,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_READ,
            'avatar' => $this->submission->owner->avatar,
            'title'  => $this->title,
            'body'   => strip_tags($this->submission->title),
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        return [
            'title' => $this->title,
            'body'  => strip_tags($this->submission->title),
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