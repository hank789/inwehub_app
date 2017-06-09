<?php namespace App\Events;

/**
 * @author: wanghui
 * @date: 2017/6/9 下午7:10
 * @email: wanghui@yonglibao.com
 */


class LogNotify extends Event {



    public $level;

    public $message;

    public $context;

    public function __construct($level,$message,$context)
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }
}