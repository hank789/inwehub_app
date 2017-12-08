<?php namespace App\Events\Frontend\System;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: wanghui@yonglibao.com
 */

/**
 * Class Push.
 */
class Push
{

    /**
     * @var
     */
    public $user_id;

    public $title;

    public $body;

    public $content;

    public $payload;

    public $template_id;


    /**
     * Push constructor.
     * @param $user_id
     * @param $title; 通知标题
     * @param $body; 通知内容
     * @param array $payload; 给前端的参数,定义事件类型和事件id
     * @param array $content;
     * @param int $template_id;模板id,默认就可以
     */
    public function __construct($user_id, $title, $body, $payload=[], $content=[], $template_id = 1)
    {
        $this->user_id = $user_id;
        $this->content = $content;
        $payload['title'] = $title;
        $this->content['payload'] = $payload;
        $this->title = $title;
        $this->body = $body?:'点击查看';
        $this->payload = $payload;
        $this->template_id = $template_id;
    }


}