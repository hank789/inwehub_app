<?php namespace App\Console\Commands\Pay;
use App\Logic\MoneyLogLogic;
use App\Models\Answer;
use App\Models\Pay\MoneyLog;
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
        $pendings = SettlementModel::where('status',SettlementModel::SETTLEMENT_STATUS_PENDING)->where('settlement_date',$date)->get();
        $ids = $pendings->pluck('id');
        SettlementModel::whereIn('id',$ids)->update([
            'status' => SettlementModel::SETTLEMENT_STATUS_PROCESS
        ]);
        foreach($pendings as $pending){
            switch($pending->source_type){
                case 'App\Models\Answer':
                    $answer = Answer::find($pending->source_id);
                    $fee = MoneyLogLogic::getAnswerFee($answer);
                    $res = MoneyLogLogic::addMoney($answer->user_id,$answer->question->price,MoneyLog::MONEY_TYPE_ANSWER,$answer,$fee,1);
                    if($res) {
                        $pending->status = SettlementModel::SETTLEMENT_STATUS_SUCCESS;
                    } else {
                        $pending->status = SettlementModel::SETTLEMENT_STATUS_FAIL;
                    }
                    break;
            }
            $pending->save();
        }

    }
}