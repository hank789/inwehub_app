<?php namespace App\Http\Controllers\Pay;
use App\Http\Controllers\Controller;
use App\Services\PayNotify;
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
    public function payNotify(Request $request)
    {
        $type = $request->get('type');
        switch($type){
            case 'wx_charge':
                $config = config('payment')['wechat'];
                break;
            case 'ali_charge':
                break;
            default:
                break;
        }
        $callback = new PayNotify();
        try {
            //$retData = Notify::getNotifyData($type, $config);// 获取第三方的原始数据，未进行签名检查
            $ret = Notify::run($type, $config, $callback);// 处理回调，内部进行了签名检查
        } catch (PayException $e) {
            echo $e->errorMessage();
            exit;
        }
        echo 'SUCCESS';
    }
}