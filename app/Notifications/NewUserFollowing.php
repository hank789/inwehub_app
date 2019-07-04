<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\SlackChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Attention;
use App\Models\Notification as NotificationModel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewUserFollowing extends Notification implements ShouldBroadcast,ShouldQueue
{
    use Queueable,InteractsWithSockets;

    protected $user_id;

    protected $attention;

    protected $notifySlack;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, Attention $attention,$notifySlack = true)
    {
        $this->user_id = $user_id;
        $this->attention = $attention;
        $this->notifySlack = $notifySlack;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [SlackChannel::class];
        if ($notifiable->checkCanDisturbNotify() && ($notifiable->site_notifications['push_rel_mine_followed']??true)){
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

    protected function getTitle(){
        $user = User::find($this->attention->user_id);
        return $user->name.'关注了你';
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $user = User::find($this->attention->user_id);
        return [
            'url'    => '/share/resume/'.$user->uuid,
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => $user->avatar,
            'title'  => $this->getTitle(),
            'body'   => '',
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $user = User::find($this->attention->user_id);
        $title = '用户'.$user->name.'关注了你';
        return [
            'title' => $title,
            'body'  => '点击前往查看',
            'payload' => ['object_type'=>'user_following','object_id'=>$user->uuid],
        ];
    }

    public function toWechatNotice($notifiable){
        $first = '又有新用户关注了你';
        $keyword2 = date('Y-m-d H:i:s',strtotime($this->attention->created_at));
        $remark = '点击查看Ta的顾问名片';
        $template_id = '24x-vyoHM0SncChmtbRv_uoPCBnI8JXFrmTsWfqccQs';
        if (config('app.env') != 'production') {
            $template_id = 'mCMHMPCPc1ceoQy66mWPee-krVmAxAB9g7kCQex6bUs';
        }
        $user = User::find($this->attention->user_id);
        $keyword1 = $user->name;
        $target_url = config('app.mobile_url').'#/share/resume/'.$user->uuid;
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

    public function toSlack($notifiable){
        if ($this->notifySlack) {
            $user = User::find($this->attention->user_id);
            $current_user = User::find($this->user_id);

            return \Slack::to(config('slack.ask_activity_channel'))
                ->disableMarkdown()
                ->send('用户'.$user->id.'['.$user->name.']关注了用户'.$current_user->id.'['.$current_user->name.']');
        }
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
