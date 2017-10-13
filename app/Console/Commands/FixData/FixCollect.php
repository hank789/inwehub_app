<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Answer;
use App\Models\Collection;
use App\Models\Pay\Order;
use App\Models\Pay\Ordergable;
use App\Models\User;
use Illuminate\Console\Command;

class FixCollect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复付费围观收藏';

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
                $user = User::find($order->user_id);
                $userCollect = $user->isCollected(get_class($answer),$answer->id);
                if (!$userCollect) {
                    Collection::create([
                        'user_id'     => $order->user_id,
                        'source_id'   => $answer->id,
                        'source_type' => get_class($answer),
                        'subject'  => '付费围观',
                    ]);
                    $answer->increment('collections');
                }
            }
        }
    }

}