<?php namespace App\Logic;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Pay\UserMoney;
use Payment\Config;
use Payment\Notify\PayNotifyInterface;

/**
 * @author: wanghui
 * @date: 2017/5/15 下午9:12
 * @email: wanghui@yonglibao.com
 */

class PayNotifyLogic implements PayNotifyInterface {

    public function notifyProcess(array $data)
    {
        $channel = $data['channel'];
        if ($channel === Config::ALI_CHARGE) {// 支付宝支付

        } elseif ($channel === Config::WX_CHARGE) {// 微信支付
            $order_no = $data['order_no'];
            $order = Order::where('order_no',$order_no)->first();
            if($order->status != Order::PAY_STATUS_SUCCESS){
                if($data['total_fee'] != $order->amount){
                    \Log::error('订单金额与返回结果不一致',['order'=>$order,'return'=>$data]);
                }
                $order->status = Order::PAY_STATUS_SUCCESS;
                $order->finish_time = date('Y-m-d H:i:s');
                $order->transaction_id = $data['transaction_id'];
                $order->response_msg = $data['trade_state'];
                $order->response_data = json_encode($data);
                $order->save();
                $return_param = $data['return_param'];
                $io = 1;
                $money_type = 1;
                switch($return_param){
                    case 'ask':
                        //付费问答
                        $io = -1;
                        $money_type = 1;
                        break;
                }
                $userMoney = UserMoney::find($order->user_id);
                //资金记录
                MoneyLog::create([
                    'user_id' => $order->user_id,
                    'change_money' => $order->amount,
                    'source_id'    => $order->id,
                    'source_type'  => get_class($order),
                    'io'           => $io,
                    'money_type'   => $money_type,
                    'before_money' => $userMoney->total_money
                ]);
            }
        } elseif ($channel === Config::CMB_CHARGE) {// 招商支付

        } elseif ($channel === Config::CMB_BIND) {// 招商签约

        } else {
            // 其它类型的通知
        }

        // 执行业务逻辑，成功后返回true
        return true;
    }

}