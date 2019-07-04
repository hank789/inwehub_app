<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Notification as NotificationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyAuth extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $company;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
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
        $title = $this->getTitle();
        return [
            'url'    => '/company/my',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_NOTICE,
            'avatar' => config('image.notice_default_icon'),
            'title'  => $title,
            'body'   => '',
            'extra_body' => ''
        ];
    }

    protected function getTitle(){
        $title = '';
        switch ($this->company->apply_status){
            case Company::APPLY_STATUS_SUCCESS:
                $title = '恭喜你成为平台认证企业！';
                break;
            case Company::APPLY_STATUS_REJECT:
                $title = '很抱歉，您的企业认证未通过审核';
                break;
        }
        return $title;
    }

    public function toPush($notifiable)
    {
        $title = '';
        switch ($this->company->apply_status){
            case Company::APPLY_STATUS_SUCCESS:
                $title = '恭喜你成为平台认证企业！';
                $object_type = 'company_auth_success';
                break;
            case Company::APPLY_STATUS_REJECT:
                $title = '很抱歉，您的企业认证未通过审核';
                $object_type = 'company_auth_fail';
                break;
        }

        return [
            'title' => $title,
            'body'  => '点击前往查看',
            'payload' => ['object_type'=>$object_type,'object_id'=>$this->company->user_id],
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
        return ['notification.user.'.$this->company->user_id];
    }
}
