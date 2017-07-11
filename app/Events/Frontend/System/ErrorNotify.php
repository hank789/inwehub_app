<?php namespace App\Events\Frontend\System;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: wanghui@yonglibao.com
 */


/**
 * Class ErrorNotify.
 */
class ErrorNotify
{

    public $message;

    public $context;




    public function __construct($message, $context = [])
    {
        $this->message = $message;
        $this->context = $context;
    }


}