<?php namespace App\Events\Frontend\Withdraw;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午3:20
 * @email: hank.huiwang@gmail.com
 */

class WithdrawProcess {


    public $withdraw_id;

    public function __construct($withdraw_id)
    {
        $this->withdraw_id = $withdraw_id;
    }

}