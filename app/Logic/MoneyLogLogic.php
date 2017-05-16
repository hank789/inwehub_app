<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午2:01
 * @email: wanghui@yonglibao.com
 */
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use Illuminate\Support\Facades\DB;

class MoneyLogLogic {

    public static function addMoney($user_id,$money,$money_type, $object_class, $fee=0){
        try{
            DB::beginTransaction();
            if($fee && $fee>$money){
                throw new \Exception('手续费大于总金额');
            }
            $userMoney = UserMoney::find($user_id);

            UserMoney::find($user_id)->increment('total_money',$money);

            //资金记录
            MoneyLog::create([
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
                MoneyLog::create([
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
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            \Log::error('增加余额失败',['data'=>func_get_args(),'msg'=>$e->getMessage()]);
            return false;
        }
    }

    //获取问答手续费
    public static function getAnswerFee($answer){
        return 0;
    }

}