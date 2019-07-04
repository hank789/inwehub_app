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

    protected $created_at;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user_id, MoneyLogModel $moneyLog, $created_at = null)
    {
        $this->user_id = $user_id;
        $this->moneyLog = $moneyLog;
        $this->created_at = $created_at;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', PushChannel::class, WechatNoticeChannel::class];
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
            case MoneyLogModel::MONEY_TYPE_PAY_FOR_VIEW_ANSWER:
                $title = '围观服务费结算到账';
                break;
            case MoneyLogModel::MONEY_TYPE_REWARD:
                $title = '分红结算到账';
                break;
            case MoneyLogModel::MONEY_TYPE_COUPON:
                $title = '红包到账';
                break;
            case MoneyLogModel::MONEY_TYPE_ASK_PAY_WALLET:
                $title = '余额支付';
                break;
            case MoneyLogModel::MONEY_TYPE_SYSTEM_ADD:
                $title = $this->moneyLog->source_type;
                break;
            case MoneyLogModel::MONEY_TYPE_QUESTION_REFUND:
                $title = '问答退款';
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
        if ($this->moneyLog->io >=1){
            $current_balance = bcadd($this->moneyLog->before_money , $this->moneyLog->change_money,2);
        } else {
            $current_balance = bcsub($this->moneyLog->before_money , $this->moneyLog->change_money,2);
        }
        return [
            'url'    => '/my/finance',
            'notification_type' => NotificationModel::NOTIFICATION_TYPE_MONEY,
            'avatar' => '',
            'name'   => $this->moneyLog->user->name,
            'title'  => $title,
            'change_money'  => bcadd($this->moneyLog->change_money,0,2),
            'before_money'  => bcadd($this->moneyLog->before_money,0,2),
            'current_balance'  => $current_balance,
            'io'     => $this->moneyLog->io,
            'body'   => '交易成功',
            'extra_body' => '感谢您对InweHub的信任!',
            'created_at' => $this->created_at
        ];
    }

    public function toPush($notifiable)
    {
        $title = $this->getTitle();
        $body = '金额：'.bcadd($this->moneyLog->change_money,0,2).'元';
        return [
            'title' => $title,
            'body'  => $body,
            'payload' => ['object_type'=>'notification_money','object_id'=>$notifiable->id],
        ];
    }

    public function toWechatNotice($notifiable){
        $title = $this->getTitle();
        $first = '您的账户资金发生以下变动!';
        $keyword2 = ($this->moneyLog->io >= 1 ? '+' : '-').bcadd($this->moneyLog->change_money,0,2).'元';
        $keyword3 = date('Y-m-d H:i:s',strtotime($this->moneyLog->updated_at));
        $target_url = config('app.mobile_url').'#/my/finance';
        $template_id = '5djK0UUvpHq9TjWFEYujXwqzf7qUR-O8_C_Wzl7W6lg';
        if (config('app.env') != 'production') {
            $template_id = 'WOt-iIVBMYJUjazUVOZ3lbGFjyO_VbpBH1sEohbnBtA';
        }
        $remark = '请点击查看详情！';
        return [
            'first'    => $first,
            'keyword1' => $title,
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
