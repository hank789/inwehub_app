<?php namespace App\Api\Controllers\Pay;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\MoneyLogLogic;
use App\Models\Activity\Coupon;
use App\Models\Answer;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Pay\Ordergable;
use App\Models\UserOauth;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Payment\Client\Charge;
use Payment\Common\PayException;
use Payment\Config;

/**
 * @author: wanghui
 * @date: 2017/5/15 下午6:54
 * @email: wanghui@yonglibao.com
 */

class PayController extends Controller {

    public function request(Request $request)
    {
        $validateRules = [
            'app_id' => 'required',
            'amount' => 'required|numeric',
            'pay_channel' => 'required|in:alipay,wxpay,appleiap,wx_pub,wx_lite',
            'pay_object_type' => 'required|in:ask,view_answer,free_ask',
            'pay_object_id'   => 'required_if:pay_object_type,view_answer'
        ];
        $this->validate($request, $validateRules);
        $loginUser = $request->user();

        if(RateLimiter::instance()->increase('pay:request',$loginUser->id,5,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $data = $request->all();
        $amount = $data['amount'];
        $pay_channel = $data['pay_channel'];
        $need_pay_actual = true;
        $use_wallet_pay = $request->input('use_wallet_pay',0);
        switch($pay_channel){
            case 'wxpay':
                if(Setting()->get('pay_method_weixin',1) != 1){
                    throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                }
                if (config('app.env') == 'production' && $loginUser->id == 3) {
                    $amount = 0.01;
                }
                $config = config('payment')['wechat'];

                $channel = Config::WX_CHANNEL_APP;
                $channel_type = Order::PAY_CHANNEL_WX_APP;
                break;
            case 'wx_pub':
                //微信公众号支付
                if(Setting()->get('pay_method_weixin',1) != 1){
                    throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                }
                //是否绑定了微信
                $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
                    ->where('user_id',$loginUser->id)->where('status',1)->orderBy('updated_at','desc')->first();
                if(!$oauthData) {
                    throw new ApiException(ApiException::USER_WEIXIN_UNOAUTH);
                }
                if (config('app.env') != 'production') {
                    $need_pay_actual = false;
                }
                if(config('app.env') == 'production' && $loginUser->id == 3){
                    $amount = 0.01;
                }
                $config = config('payment')['wechat_pub'];

                $channel = Config::WX_CHANNEL_PUB;
                $channel_type = Order::PAY_CHANNEL_WX_PUB;
                break;
            case 'wx_lite':
                //微信小程序支付
                if(Setting()->get('pay_method_weixin',1) != 1){
                    throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                }
                //是否绑定了微信
                $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEAPP)
                    ->where('user_id',$loginUser->id)->where('status',1)->orderBy('updated_at','desc')->first();
                if(!$oauthData) {
                    throw new ApiException(ApiException::USER_WEIXIN_UNOAUTH);
                }
                if (config('app.env') != 'production') {
                    $need_pay_actual = false;
                }
                if(config('app.env') == 'production' && $loginUser->id == 3){
                    $amount = 0.01;
                }
                $config = config('payment')['wechat_lite'];

                $channel = Config::WX_CHANNEL_LITE;
                $channel_type = Order::PAY_CHANNEL_WX_LITE;
                break;
            case 'alipay':
                if(Setting()->get('pay_method_ali',0) != 1){
                    throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                }
                $channel_type = Order::PAY_CHANNEL_ALIPAY_APP;
                break;
            case 'appleiap':
                if(Setting()->get('pay_method_iap',0) != 1){
                    throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                }
                $config = config('payment.iap');
                $ids = $config['ids'];
                if ($data['pay_object_type'] == 'view_answer') {
                    $iap_id = $ids['qa_see1'];
                } else {
                    $iap_id = $ids[$amount]??0;
                }
                $channel_type = Order::PAY_CHANNEL_IOS_IAP;
                break;
            default:
                throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                break;
        }
        switch($data['pay_object_type']){
            case 'ask':
                $subject = 'Inwehub-付费提问';
                $body = $subject;
                break;
            case 'view_answer':
                $subject = 'Inwehub-付费围观';
                $body = $subject;
                $pay_object_id = $data['pay_object_id'];
                $answer = Answer::findOrFail($pay_object_id);
                $order = $answer->orders()->where('user_id',$loginUser->id)->where('return_param','view_answer')->first();
                if ($order && $order->status == Order::PAY_STATUS_SUCCESS) {
                    //已经付过款
                    return self::createJsonData(true,[
                        'order_info' => [],
                        'pay_channel' => $pay_channel,
                        'order_id'    => $order->id,
                        'debug'       => 1
                    ]);
                }
                break;
            case 'free_ask':
                //免费问答
                $subject = 'Inwehub-免费提问';
                $body = $subject;
                $need_pay_actual = false;
                $amount = 0;
                break;
            default:
                throw new ApiException(ApiException::PAYMENT_UNKNOWN_PAY_TYPE);
                break;
        }

        $orderNo = gen_order_number();
        if ($use_wallet_pay && $amount > 0) {
            $user_total_money = $loginUser->getAvailableTotalMoney();
            if ($user_total_money > 0) {
                if ($user_total_money >= $amount) {
                    //钱包金额足够，不必使用第三方支付
                    $wallet_money = $amount;
                    $amount = 0;
                    $need_pay_actual = false;
                } else {
                    $wallet_money = $user_total_money;
                    $amount = bcsub($amount,$user_total_money,2);
                    //60秒内锁住这部分金额
                    $loginUser->lockMoney($wallet_money,60);
                }
                //使用钱包支付的金额
                $order1 = Order::create([
                    'user_id' => $loginUser->id,
                    'body'    => $body,
                    'subject'    => $subject,
                    'order_no'    => $orderNo.'W',
                    'timeout_express' => time() + 600,// 表示必须 600s 内付款
                    'amount'    => $wallet_money,// 微信沙箱模式，需要金额固定为3.01
                    'actual_amount' => $wallet_money,//实际支付金额
                    'return_param' => $data['pay_object_type'],
                    'pay_channel'  => Order::PAY_CHANNEL_WALLET,
                    'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
                ]);
                if ($need_pay_actual == false && $amount <=0) {
                    //使用钱包足额支付，马上减少余额
                    MoneyLogLogic::decMoney($loginUser->id,$wallet_money,MoneyLog::MONEY_TYPE_ASK_PAY_WALLET,$order1);
                }
            } else {
                throw new ApiException(ApiException::PAYMENT_SYSTEM_ERROR);
            }
        }

        // 订单信息
        $payData = [
            'user_id' => $loginUser->id,
            'body'    => $body,
            'subject'    => $subject,
            'order_no'    => $orderNo,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount'    => $amount,// 微信沙箱模式，需要金额固定为3.01
            'actual_amount' => $amount,//实际支付金额
            'return_param' => $data['pay_object_type'],
            'pay_channel'  => $channel_type,
            'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
        ];

        $order = Order::create($payData);
        //首次提问
        if($data['pay_object_type'] == 'ask' && $request->input('amount') == 1)
        {
            $coupon = Coupon::where('user_id',$loginUser->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->first();
            if($coupon && $coupon->coupon_status == Coupon::COUPON_STATUS_PENDING){
                $coupon->used_object_type = get_class($order);
                $coupon->used_object_id = $order->id;
                $coupon->save();
            } else {
                $order->status = Order::PAY_STATUS_QUIT;
                $order->save();
                if (isset($order1)) {
                    $order1->status = Order::PAY_STATUS_QUIT;
                    $order1->save();
                }
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        }

        if(Setting()->get('need_pay_actual',1) != 1 || $need_pay_actual==false) {
            $order->status = Order::PAY_STATUS_SUCCESS;
            $order->finish_time = date('Y-m-d H:i:s');
            $order->save();
            if (isset($order1)) {
                $order1->status = Order::PAY_STATUS_SUCCESS;
                $order1->finish_time = date('Y-m-d H:i:s');
                $order1->save();
            }
            //如果开启了非强制支付
            return self::createJsonData(true,[
                'order_info' => [],
                'pay_channel' => $pay_channel,
                'order_id'    => $order->id,
                'debug'       => 1
            ]);
        }
        $return = [];
        try {
            if($pay_channel == 'appleiap'){
                $ret = ['productid'=>$iap_id];
                $return['iap_ids'] = array_values($ids);
            } else {
                switch($pay_channel){
                    case 'wx_lite':
                    case 'wx_pub':
                        //微信公众号支付
                        $payData['openid'] = $oauthData->openid;
                        $payData['product_id'] = $order->id;
                        break;
                }
                $ret = Charge::run($channel, $config, $payData);
                if ($pay_channel == 'wx_pub') {
                    $ret = json_encode($ret);
                }
            }
            $order->status = Order::PAY_STATUS_PROCESS;
            $order->save();
            if (isset($order1)) {
                $order1->status = Order::PAY_STATUS_PROCESS;
                $order1->save();
            }
        } catch (PayException $e) {
            return self::createJsonData(false,[],$e->getCode(),$e->getMessage());
        }

        $return['order_info'] = $ret;
        $return['pay_channel'] = $pay_channel;
        $return['order_id'] = $order->id;
        $return['debug'] = 0;

        return self::createJsonData(true,$return);

    }

}