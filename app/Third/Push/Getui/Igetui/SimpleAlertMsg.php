<?php namespace App\Third\Push\Getui\Igetui;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-4-10
 * Time: 上午11:37
 */

class SimpleAlertMsg implements ApnMsg{
    var $alertMsg;

    public function get_alertMsg() {
        return $this->alertMsg;
    }
}
