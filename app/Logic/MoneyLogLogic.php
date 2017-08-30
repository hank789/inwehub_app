<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午2:01
 * @email: wanghui@yonglibao.com
 */
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Pay\UserMoney;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Support\Facades\DB;
use App\Notifications\MoneyLog as MoneyLogNotify;

class MoneyLogLogic {

    public static function addMoney($user_id,$money,$money_type, $object_class, $fee=0, $is_settlement = 0){
        try{
            DB::beginTransaction();
            if($fee && $fee>$money){
                throw new \Exception('手续费大于总金额');
            }
            $userMoney = UserMoney::find($user_id);

            UserMoney::find($user_id)->increment('total_money',$money);
            //执行结算
            if($is_settlement){
                UserMoney::find($user_id)->decrement('settlement_money',$money);
            }

            //资金记录
            $moneyLog1 = MoneyLog::create([
                'user_id' => $user_id,
                'change_money' => $money,
                'source_id'    => $object_class->id,
                'source_type'  => get_class($object_class),
                'io'           => 1,
                'money_type'   => $money_type,
                'before_money' => $userMoney->total_money
            ]);
            if($fee>0){
                $userMoney = UserMoney::find($user_id);
                UserMoney::find($user_id)->decrement('total_money',$fee);
                $moneyLog2 = MoneyLog::create([
                    'user_id' => $user_id,
                    'change_money' => $fee,
                    'source_id'    => $object_class->id,
                    'source_type'  => get_class($object_class),
                    'io'           => -1,
                    'money_type'   => MoneyLog::MONEY_TYPE_FEE,
                    'before_money' => $userMoney->total_money
                ]);
            }
            DB::commit();
            if ($is_settlement) {
                $user = User::find($user_id);
                $settlement_count = RateLimiter::instance()->increaseBy('settlement_count_'.$user_id, date('Y-m-d'),1,3600*24*5);
                $user->notify(new MoneyLogNotify($user_id,$moneyLog1,date('Y-m-d H:i:s',strtotime('+'.$settlement_count.' seconds'))));
                if (isset($moneyLog2)) {
                    $settlement_count = RateLimiter::instance()->increaseBy('settlement_count_'.$user_id, date('Y-m-d'),1,3600*24*5);
                    $user->notify(new MoneyLogNotify($user_id,$moneyLog2,date('Y-m-d H:i:s',strtotime('+'.$settlement_count.' seconds'))));
                }
            }
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            \Log::error('增加余额失败',['data'=>func_get_args(),'msg'=>$e->getMessage()]);
            return false;
        }
    }

    public static function decMoney($user_id,$money,$money_type, $object_class, $fee=0, $log_status=1){
        try{
            DB::beginTransaction();
            if($fee && $fee>$money){
                throw new \Exception('手续费大于总金额');
            }
            $userMoney = UserMoney::find($user_id);

            UserMoney::find($user_id)->decrement('total_money',$money);

            //资金记录
            MoneyLog::create([
                'user_id' => $user_id,
                'change_money' => $money,
                'source_id'    => $object_class->id,
                'source_type'  => get_class($object_class),
                'io'           => -1,
                'status'       => $log_status,
                'money_type'   => $money_type,
                'before_money' => $userMoney->total_money
            ]);
            if($fee>0){
                $userMoney = UserMoney::find($user_id);
                UserMoney::find($user_id)->decrement('total_money',$fee);
                MoneyLog::create([
                    'user_id' => $user_id,
                    'change_money' => $fee,
                    'source_id'    => $object_class->id,
                    'source_type'  => get_class($object_class),
                    'io'           => -1,
                    'status'       => $log_status,
                    'money_type'   => MoneyLog::MONEY_TYPE_FEE,
                    'before_money' => $userMoney->total_money
                ]);
            }
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            \Log::error('扣除余额失败',['data'=>func_get_args(),'msg'=>$e->getMessage()]);
            return false;
        }
    }

    //获取问答手续费
    public static function getAnswerFee($answer){
        //目前都是20%的手续费
        $fee_rate = Setting()->get('pay_answer_normal_fee_rate',0.2);
        //是否使用iap支付
        $question = $answer->question;
        $order = $question->orders()->get()->last();
        switch($order->pay_channel){
            case Order::PAY_CHANNEL_IOS_IAP:
                $iap_fee_rate = Setting()->get('pay_answer_iap_fee_rate',0.32);
                $fee_rate += $iap_fee_rate;
                break;
        }
        return bcmul($fee_rate ,$answer->question->price,2);
    }

}