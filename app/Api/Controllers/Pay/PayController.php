<?php namespace App\Api\Controllers\Pay;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Pay\Order;
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
            'amount' => 'required|integer',
            'pay_channel' => 'required|in:alipay,wxpay,appleiap,wx_pub',
            'pay_object_type' => 'required|in:ask'
        ];
        $this->validate($request, $validateRules);
        $loginUser = $request->user();

        if(RateLimiter::instance()->increase('pay:request',$loginUser->id,2,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        \Log::info('pay_request',$request->all());


        $data = $request->all();
        $amount = $data['amount'];
        $pay_channel = $data['pay_channel'];
        switch($pay_channel){
            case 'wxpay':
                if(Setting()->get('pay_method_weixin',1) != 1){
                    throw new ApiException(ApiException::PAYMENT_UNKNOWN_CHANNEL);
                }
                if (config('app.env') != 'production') {
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
                    $amount = 0.01;
                }
                $config = config('payment')['wechat_pub'];

                $channel = Config::WX_CHANNEL_PUB;
                $channel_type = Order::PAY_CHANNEL_WX_PUB;
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
                $iap_id = $ids[$amount];
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
            default:
                throw new ApiException(ApiException::PAYMENT_UNKNOWN_PAY_TYPE);
                break;
        }
        $orderNo = gen_order_number();

        // 订单信息
        $payData = [
            'user_id' => $loginUser->id,
            'body'    => $body,
            'subject'    => $subject,
            'order_no'    => $orderNo,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount'    => $amount,// 微信沙箱模式，需要金额固定为3.01
            'return_param' => $data['pay_object_type'],
            'pay_channel'  => $channel_type,
            'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
        ];

        $order = Order::create($payData);

        if(Setting()->get('need_pay_actual',1) != 1) {
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
                    case 'wx_pub':
                        //微信公众号支付
                        $payData['openid'] = $oauthData->openid;
                        $payData['product_id'] = $order->id;
                        break;
                }
                $ret = Charge::run($channel, $config, $payData);
            }
            $order->status = Order::PAY_STATUS_PROCESS;
            $order->save();
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