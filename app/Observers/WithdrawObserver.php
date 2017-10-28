<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Pay\Withdraw;
use Illuminate\Contracts\Queue\ShouldQueue;

class WithdrawObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;


    public function creating(Withdraw $withdraw)
    {
        $fields[] = [
            'title' => '用户',
            'value' => $withdraw->user->name,
            'short' => true
        ];

        $fields[] = [
            'title' => '手机号',
            'value' => $withdraw->user->mobile,
            'short' => true
        ];


        $fields[] = [
            'title' => '金额',
            'value' => $withdraw->amount,
            'short' => true
        ];

        $fields[] = [
            'title' => '提现通道',
            'value' => $withdraw->getWithdrawChannelName(),
            'short' => true
        ];

        $status= '待审核';
        $title = '用户'.$withdraw->user->id.'['.$withdraw->user->name.']提交了提现申请';
        $color = 'good';
        switch($withdraw->status){
            case Withdraw::WITHDRAW_STATUS_PENDING:
                $status= '待审核';
                break;
            case Withdraw::WITHDRAW_STATUS_PROCESS:
                $status= '处理中';
                break;
            case Withdraw::WITHDRAW_STATUS_SUCCESS:
                $title = '开始处理提现';
                $status= '处理成功';
                break;
            case Withdraw::WITHDRAW_STATUS_FAIL:
                $title = '开始处理提现';
                $status= '处理失败';
                $color = 'danger';
                break;

        }
        $fields[] = [
            'title' => '提现状态',
            'value' => $status,
            'short' => true
        ];

        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'     => $color,
                    'fields' => $fields
                ]
            )->send($title);
    }

    public function updated(Withdraw $withdraw){
        $this->creating($withdraw);
    }

}