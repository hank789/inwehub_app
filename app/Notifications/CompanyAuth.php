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
            'avatar' => config('image.user_default_avatar'),
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
            'body'  => '',
            'payload' => ['object_type'=>$object_type,'object_id'=>$this->company->user_id],
        ];
    }

    public function toWechatNotice($notifiable){

        return [
            'content' => '企业账户申请认证',
            'object_type'  => 'company_auth',
            'object_id' => $this->company->user_id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->company->user_id];
    }
}
