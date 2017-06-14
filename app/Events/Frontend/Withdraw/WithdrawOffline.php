<?php namespace App\Events\Frontend\Withdraw;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午3:20
 * @email: wanghui@yonglibao.com
 */

class WithdrawOffline {


    public $withdraw_id;

    public function __construct($withdraw_id)
    {
        $this->withdraw_id = $withdraw_id;
    }

}