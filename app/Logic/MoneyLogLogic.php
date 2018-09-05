<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午2:01
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Pay\Settlement;
use App\Models\Pay\UserMoney;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Support\Facades\DB;
use App\Notifications\MoneyLog as MoneyLogNotify;

class MoneyLogLogic {

    public static function addMoney($user_id,$money,$money_type, $object_class, $fee=0, $is_settlement = 0, $notify = false){
        try{
            DB::beginTransaction();
            if($fee && $fee>$money){
                throw new \Exception('手续费大于总金额');
            }
            $userMoney = UserMoney::find($user_id);
            $before_money = $userMoney->total_money;

            $userMoney->total_money = bcadd($userMoney->total_money, $money,2);
            //执行结算
            if($is_settlement){
                $userMoney->settlement_money = bcsub($userMoney->settlement_money,$money,2);
            }
            if ($money_type == MoneyLog::MONEY_TYPE_REWARD) {
                //分红收入
                $userMoney->reward_money = bcadd($userMoney->reward_money, $money,2);
            }

            //资金记录
            $moneyLog1 = MoneyLog::create([
                'user_id' => $user_id,
                'change_money' => $money,
                'source_id'    => $object_class->id,
                'source_type'  => get_class($object_class),
                'io'           => 1,
                'money_type'   => $money_type,
                'before_money' => $before_money
            ]);
            if($fee>0){
                $before_money2 = $userMoney->total_money;
                $userMoney->total_money = bcsub($userMoney->total_money, $fee,2);
                $moneyLog2 = MoneyLog::create([
                    'user_id' => $user_id,
                    'change_money' => $fee,
                    'source_id'    => $object_class->id,
                    'source_type'  => get_class($object_class),
                    'io'           => -1,
                    'money_type'   => MoneyLog::MONEY_TYPE_FEE,
                    'before_money' => $before_money2
                ]);
            }
            $userMoney->save();
            $reward_source_type = '';
            switch ($money_type) {
                case MoneyLog::MONEY_TYPE_ANSWER:
                    //回答收入分红,回答者的实际收入*分红利率，从平台扣这笔钱，回答者不影响
                    $reward_source_type = Settlement::SOURCE_TYPE_REWARD_ANSWER;
                    break;
                case MoneyLog::MONEY_TYPE_PAY_FOR_VIEW_ANSWER:
                    //付费围观收入分红,实际收入*分红利率，从平台扣这笔钱，不影响收入者
                    $reward_source_type = Settlement::SOURCE_TYPE_REWARD_PAY_FOR_VIEW_ANSWER;
                    break;
                case MoneyLog::MONEY_TYPE_COUPON:
                    //现金红包分红
                    $reward_source_type = Settlement::SOURCE_TYPE_REWARD_COUPON;
                    $is_settlement = 1;
                    break;
            }
            //分红处理
            $user = User::find($user_id);
            if ($reward_source_type && $user->rc_uid) {
                $reward_user = User::find($user->rc_uid);
                $reward_settlement_money = bcmul(bcsub($money, $fee,2),Settlement::getInviteRewardRate(),2);
                //每月月底结算
                $settlement_date = date('Y-m-d',strtotime(date('Y-m-1').' +1 month -1 day'));
                $today = date('Y-m-d');
                //如果今天是月底，则延后一天结算
                if ($settlement_date == $today) {
                    $settlement_date = date('Y-m-d',strtotime(' +1 day'));
                }
                $object = Settlement::create([
                    'user_id' => $reward_user->id,
                    'source_id' => $object_class->id,
                    'source_type' => $reward_source_type,
                    'actual_amount' => $reward_settlement_money,
                    'actual_fee' => 0,
                    'settlement_date' => $settlement_date,
                    'status' => Settlement::SETTLEMENT_STATUS_PENDING
                ]);
                if ($object){
                    $reward_user_money = $reward_user->userMoney;
                    $reward_user_money->settlement_money = bcadd($reward_user_money->settlement_money,$reward_settlement_money,2);
                    $reward_user_money->save();
                }
            }
            DB::commit();
            if ($is_settlement || $notify) {
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
            app('sentry')->captureException($e);
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
            //资金记录
            $moneyLog1 = MoneyLog::create([
                'user_id' => $user_id,
                'change_money' => $money,
                'source_id'    => $object_class->id,
                'source_type'  => get_class($object_class),
                'io'           => -1,
                'status'       => $log_status,
                'money_type'   => $money_type,
                'before_money' => $userMoney->total_money
            ]);
            $userMoney->total_money = bcsub($userMoney->total_money,$money,2);
            if($fee>0){
                $userMoney = UserMoney::find($user_id);
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
                $userMoney->total_money = bcsub($userMoney->total_money,$fee,2);
            }
            $userMoney->save();
            DB::commit();
            if ($log_status == 1) {
                $user = User::find($user_id);
                $user->notify(new MoneyLogNotify($user_id,$moneyLog1));
            }
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            app('sentry')->captureException($e);
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