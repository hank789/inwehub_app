<?php

namespace App\Jobs;

use App\Logic\MoneyLogLogic;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * 悬赏问答退款操作
 * Class QuestionRefund
 * @package App\Jobs
 */
class QuestionRefund implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $queue = 'withdraw';

    public $questionId;



    public function __construct($questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $question = Question::find($this->questionId);
        //已经有回答的不退款
        if ($question->answers >= 1) return;
        //修改问题状态为已关闭
        if ($question->status == 4) {
            $question->status = 8;
            $question->save();
        }
        $orders = $question->orders->where('status',Order::PAY_STATUS_SUCCESS)->all();
        if (empty($orders)) return;
        foreach ($orders as $order) {
            //直接退款到余额
            $order->status = Order::PAY_STATUS_REFUND;
            $order->save();
            if ($order->actual_amount > 0) {
                MoneyLogLogic::addMoney($order->user_id,$order->actual_amount,MoneyLog::MONEY_TYPE_QUESTION_REFUND,$order,0,0,true);
            }
        }
    }
}
