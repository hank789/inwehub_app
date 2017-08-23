<?php

namespace App\Notifications;

use App\Channels\PushChannel;
use App\Channels\WechatNoticeChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Models\Notification as NotificationModel;
use App\Models\Pay\MoneyLog as MoneyLogModel;

class MoneyLog extends Notification implements ShouldQueue,ShouldBroadcast
{
    use Queueable,InteractsWithSockets;

    protected $user_id;

    protected $moneyLog;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, MoneyLogModel $moneyLog)
    {
        $this->user_id = $user_id;
        $this->moneyLog = $moneyLog;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast',PushChannel::class, WechatNoticeChannel::class];
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
        $title = '';

        switch($this->moneyLog->money_type){
            case MoneyLogModel::MONEY_TYPE_ANSWER:
                $title = '问答服务费结算到账';
                break;
            case MoneyLogModel::MONEY_TYPE_ASK:
                $title = '付费问答';
                break;
            case MoneyLogModel::MONEY_TYPE_FEE:
                $title = '手续费扣除成功';
                break;
            case MoneyLogModel::MONEY_TYPE_WITHDRAW:
                $title = '提现处理成功';
                break;
        }
        return $title;
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
            'url'    => '/my/finance',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_MONEY,
            'avatar' => '',
            'name'   => $this->moneyLog->user->name,
            'title'  => $title,
            'change_money'  => $this->moneyLog->change_money,
            'before_money'  => $this->moneyLog->before_money,
            'io'     => $this->moneyLog->io,
            'body'   => '',
            'extra_body' => ''
        ];
    }

    public function toPush($notifiable)
    {
        $title = $this->getTitle();
        $body = '金额：'.$this->moneyLog->change_money.'元';
        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>'notification_money','object_id'=>$notifiable->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $title = $this->getTitle();
        $object_type = '';
        switch($this->moneyLog->money_type){
            case MoneyLogModel::MONEY_TYPE_ANSWER:
                $object_type = 'notification_money_settlement';
                break;
            case MoneyLogModel::MONEY_TYPE_FEE:
                $object_type = 'notification_money_fee';
                break;
        }
        return [
            'content' => $title,
            'object_type'  => $object_type,
            'object_id' => $this->moneyLog->id,
        ];
    }

    public function broadcastOn(){
        return ['notification.user.'.$this->user_id];
    }
}
