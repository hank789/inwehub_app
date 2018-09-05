<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Jobs\SaveActivity;
use App\Models\Doing;
use App\Models\Pay\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AddPayForViewDoing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:add_pay_for_view_doing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '付费围观doing事件修复';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = Order::where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->get();
        foreach ($orders as $order) {
            $answer = $order->answer()->first();
            if ($answer) {
                dispatch(new SaveActivity(
                    [
                        'user_id' => $order->user_id,
                        'action' => Doing::ACTION_PAY_FOR_VIEW_ANSWER,
                        'source_id' => $answer->id,
                        'source_type' => get_class($answer),
                        'subject' => '付费围观答案',
                        'content' => '',
                        'refer_id' => 0,
                        'refer_user_id' => $answer->user_id,
                        'refer_content' => '',
                        'created_at' => $order->created_at
                    ]
                ));
            }
        }
    }

}