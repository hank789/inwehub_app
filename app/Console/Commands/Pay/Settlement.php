<?php namespace App\Console\Commands\Pay;
use App\Logic\MoneyLogLogic;
use App\Models\Answer;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use Illuminate\Console\Command;
use App\Models\Pay\Settlement as SettlementModel;
/**
 * @author: wanghui
 * @date: 2017/5/22 下午8:21
 * @email: wanghui@yonglibao.com
 */

class Settlement extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pay:settlement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '执行结算逻辑';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = date('Y-m-d 00:00:00');
        $pendings = SettlementModel::where('status',SettlementModel::SETTLEMENT_STATUS_PENDING)
            ->where('settlement_date',$date)
            ->get();
        $ids = $pendings->pluck('id');
        SettlementModel::whereIn('id',$ids)->update([
            'status' => SettlementModel::SETTLEMENT_STATUS_PROCESS
        ]);

        foreach($pendings->groupBy('user_id') as $user_id => $items){
            $pay_for_view_amount = [];
            $pay_for_view_fee = [];
            foreach ($items as $pending) {
                switch($pending->source_type){
                    case 'App\Models\Answer':
                        $answer = Answer::find($pending->source_id);
                        $fee = MoneyLogLogic::getAnswerFee($answer);
                        $res = MoneyLogLogic::addMoney($answer->user_id,$answer->question->price,MoneyLog::MONEY_TYPE_ANSWER,$answer,$fee,1);
                        if($res) {
                            $pending->actual_amount = $answer->question->price;
                            $pending->actual_fee    = $fee;
                            $pending->actual_settlement_date = date('Y-m-d H:i:s');
                            $pending->status = SettlementModel::SETTLEMENT_STATUS_SUCCESS;
                        } else {
                            $pending->status = SettlementModel::SETTLEMENT_STATUS_FAIL;
                        }
                        $pending->save();
                        break;
                    case 'App\Models\Pay\Order':
                        $order = Order::find($pending->source_id);
                        switch ($order->return_param) {
                            case 'view_answer':
                                //付费围观
                                $answer = $order->answer()->first();
                                $pay_for_view_amount[$answer->id] = bcadd($pay_for_view_amount[$answer->id]??0,$pending->actual_amount,2);
                                $pay_for_view_fee[$answer->id] = bcadd($pay_for_view_fee[$answer->id]??0,$pending->actual_fee,2);
                                $pending->actual_settlement_date = date('Y-m-d H:i:s');
                                $pending->status = SettlementModel::SETTLEMENT_STATUS_SUCCESS;
                                $pending->save();
                                break;
                        }
                        break;
                }
            }
            foreach ($pay_for_view_amount as $answer_id=>$amount){
                $answer = Answer::find($answer_id);
                MoneyLogLogic::addMoney($user_id,$amount,MoneyLog::MONEY_TYPE_PAY_FOR_VIEW_ANSWER,$answer,$pay_for_view_fee[$answer_id],1);
            }

        }

    }
}