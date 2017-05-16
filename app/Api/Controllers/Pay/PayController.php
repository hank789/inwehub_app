<?php namespace App\Api\Controllers\Pay;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Pay\Order;
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
            'pay_channel' => 'required|in:alipay,wxpay',
            'pay_object_type' => 'required|in:ask'
        ];
        $this->validate($request, $validateRules);
        $data = $request->all();
        $pay_channel = $data['pay_channel'];
        switch($pay_channel){
            case 'wxpay':
                $config = config('payment')['wechat'];
                $channel = Config::WX_CHANNEL_APP;
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
        $orderNo = gen_payment_order_number();
        // 订单信息
        $payData = [
            'body'    => $body,
            'subject'    => $subject,
            'order_no'    => $orderNo,
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount'    => $data['amount'],// 微信沙箱模式，需要金额固定为3.01
            'return_param' => $data['pay_object_type'],
            'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
        ];

        $order = Order::create($payData);

        try {
            $ret = Charge::run($channel, $config, $payData);
        } catch (PayException $e) {
            return self::createJsonData(false,[],$e->getCode(),$e->getMessage());
        }

        $return = [];
        $return['order_info'] = $ret;
        $return['pay_channel'] = $pay_channel;
        $return['order_id'] = $order->id;

        return self::createJsonData(true,$return);

    }

}