<?php namespace App\Events\Frontend\System;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: wanghui@yonglibao.com
 */


/**
 * Class ErrorNotify.
 */
class SystemNotify
{

    public $message;

    public $fields;



    public function __construct($message, $fields = [])
    {
        $this->message = $message;
        $this->fields = $fields;
    }


}