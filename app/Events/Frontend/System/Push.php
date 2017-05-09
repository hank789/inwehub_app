<?php namespace App\Events\Frontend\System;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered.
 */
class Push
{

    use SerializesModels;

    /**
     * @var
     */
    public $user;

    public $title;

    public $body;

    public $content;

    public $payload;

    public $template_id;


    public function __construct($user,$title, $content,$body, $payload=[], $template_id = 4)
    {
        $this->user = $user;
        $this->content = $content;
        $this->title = $title;
        $this->content = $content;
        $this->body = $body;
        $this->payload = $payload;
        $this->template_id = $template_id;
    }


}