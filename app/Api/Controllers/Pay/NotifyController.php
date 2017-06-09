<?php namespace App\Api\Controllers\Pay;
use App\Api\Controllers\Controller;
use App\Logic\PayNotifyLogic;
use Illuminate\Http\Request;
use Payment\Client\Notify;
use Payment\Common\PayException;

/**
 * @author: wanghui
 * @date: 2017/5/15 下午8:59
 * @email: wanghui@yonglibao.com
 */

class NotifyController extends Controller
{
    public function payNotify($type,Request $request)
    {
        \Log::info('pay_notify_type',[$type]);
        \Log::info('pay_notify',$request->all());
        \Log::info('pay_notify_data',[@file_get_contents('php://input')]);
        switch($type){
            case 'wx_charge':
                $config = config('payment')['wechat'];
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
            echo $e->errorMessage();
            exit;
        }
        return $ret;
    }
}