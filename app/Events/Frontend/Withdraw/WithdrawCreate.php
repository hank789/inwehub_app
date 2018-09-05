<?php namespace App\Events\Frontend\Withdraw;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午3:20
 * @email: hank.huiwang@gmail.com
 */

class WithdrawCreate {


    public $user_id;
    public $amount;
    public $client_ip;

    public function __construct($user_id, $amount, $client_ip)
    {
        $this->user_id = $user_id;
        $this->amount = $amount;
        $this->client_ip = $client_ip;
    }

}