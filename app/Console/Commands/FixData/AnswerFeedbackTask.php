<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Logic\TaskLogic;
use App\Models\Answer;
use App\Models\Pay\Order;
use App\Models\Pay\Ordergable;
use App\Models\Task;
use Illuminate\Console\Command;

class AnswerFeedbackTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:answer_feedback_task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '付费围观生成点评通知';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = Order::where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->get();
        foreach ($orders as $order) {
            $order_gable = Ordergable::where('pay_order_id',$order->id)->where('pay_order_gable_type','App\Models\Answer')->first();
            if ($order_gable) {
                $answer = Answer::find($order_gable->pay_order_gable_id);
                TaskLogic::task($order->user_id,get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK);
            }
        }
    }

}