<?php namespace App\Services;
use Payment\Config;
use Payment\Notify\PayNotifyInterface;

/**
 * @author: wanghui
 * @date: 2017/5/15 下午9:12
 * @email: wanghui@yonglibao.com
 */

class PayNotify implements PayNotifyInterface {

    public function notifyProcess(array $data)
    {
        $channel = $data['channel'];
        if ($channel === Config::ALI_CHARGE) {// 支付宝支付

        } elseif ($channel === Config::WX_CHARGE) {// 微信支付

        } elseif ($channel === Config::CMB_CHARGE) {// 招商支付

        } elseif ($channel === Config::CMB_BIND) {// 招商签约

        } else {
            // 其它类型的通知
        }

        // 执行业务逻辑，成功后返回true
        return true;
    }

}