<?php namespace App\Logic;
use App\Models\Pay\Order;
use Payment\Client\Query;
use Payment\Config;

/**
 * @author: wanghui
 * @date: 2017/6/30 下午3:01
 * @email: hank.huiwang@gmail.com
 */

class PayQueryLogic {
    public static function queryWechatPayOrder($orderId){
        $order = Order::find($orderId);
        //查询一次订单状态
        switch($order->pay_channel){
            case Order::PAY_CHANNEL_WX_PUB:
                $pay_config = config('payment')['wechat_pub'];
                break;
            case Order::PAY_CHANNEL_WX_APP:
                $pay_config = config('payment')['wechat'];
                break;
        }
        if(isset($pay_config)){
            try{
                $ret = Query::run(Config::WX_CHARGE,$pay_config,['out_trade_no'=>$order->order_no]);
                if(isset($ret['is_success']) && $ret['is_success']=='T'){
                    //支付成功
                    return true;
                }
            } catch (\Exception $e){
                \Log::error('查询微信支付订单失败',['msg'=>$e->getMessage(),'order'=>$order]);
            }
        }
        return false;
    }
}