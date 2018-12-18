<?php namespace App\Api\Controllers\Pay;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\PayNotifyLogic;
use App\Models\AppVersion;
use App\Models\Pay\Order;
use App\Services\ItunesReceiptValidator;
use Illuminate\Http\Request;
use Payment\Client\Notify;
use Payment\Common\PayException;

/**
 * @author: wanghui
 * @date: 2017/5/15 下午8:59
 * @email: hank.huiwang@gmail.com
 */

class NotifyController extends Controller
{
    public function payNotify($type,Request $request)
    {
        switch($type){
            case 'wx_charge':
                $config = config('payment')['wechat'];
                break;
            case 'wx_pub_charge':
                $config = config('payment')['wechat_pub'];
                $type = 'wx_charge';
                break;
            case 'wx_lite_charge':
                $config = config('payment')['wechat_lite'];
                $type = 'wx_charge';
                break;
            case 'ali_charge':
                return 'false';
                break;
            default:
                return 'false';
                break;
        }

        $callback = new PayNotifyLogic();
        try {
            //$retData = Notify::getNotifyData($type, $config);// 获取第三方的原始数据，未进行签名检查
            $ret = Notify::run($type, $config, $callback);// 处理回调，内部进行了签名检查
        } catch (PayException $e) {
            \Log::error('wx_pay_notify_error',['msg'=>$e->getMessage()]);
            echo $e->errorMessage();
            exit;
        }
        return $ret;
    }

    public function iapNotify(Request $request){
        \Log::info('iap_notify',$request->all());
        $validateRules = [
            'orderId'           => 'required',
            'transactionReceipt' => 'required',//购买商品的交易收据
            'transactionState'   => 'required',//购买商品的交易状态,可取值："1"为支付成功；"2"为支付失败；"3"为支付已恢复。
            'transactionIdentifier' => 'required',//购买商品的交易订单标识
            'transactionDate'    => 'required',//购买商品的交易日期
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();
        $endpoint = ItunesReceiptValidator::PRODUCTION_URL;
        $user = $request->user();
        //苹果审核测试账户用沙箱支付
        if (in_array($user->id,[504])){
            $endpoint = ItunesReceiptValidator::SANDBOX_URL;
        }
        /*$pending_version = AppVersion::where('status',0)->orderBy('app_version','desc')->first();
        $current_version = $request->input('current_version');
        //苹果待审核版本使用沙箱支付
        if($pending_version && $pending_version->app_version == $current_version){
            $endpoint = ItunesReceiptValidator::SANDBOX_URL;
        }*/

        $rv = new ItunesReceiptValidator($endpoint, $data['transactionReceipt']);
        \Log::info('Environment: ' .
            ($rv->getEndpoint() === ItunesReceiptValidator::SANDBOX_URL) ? 'Sandbox' : 'Production' .
            '<br />');
        $info = $rv->validateReceipt();
        \Log::info('iap_notify_result',[$info]);
        $config = config('payment.iap');
        $ids = $config['ids'];
        if(!in_array($info->product_id,$ids) || $info->bid != 'com.inwehub.InwehubApp'){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $callback = new PayNotifyLogic();
        $product = $data['payment']['productid'];

        $amount = 0;
        foreach($ids as $key=>$value){
            if($value == $product) $amount = $key;
        }
        if ($product == 'qa_see1') $amount = 1;
        $ret_data = [
            'channel' => Order::PAY_CHANNEL_IOS_IAP,
            'orderId' => $data['orderId'],
            'amount'  => $amount,
            'transaction_id' => $data['transactionIdentifier'],
            'trade_state'    => $data['transactionState'],
            'origin_data'    => $data
        ];
        $callback->notifyProcess($ret_data);
        return self::createJsonData(true);
    }

}