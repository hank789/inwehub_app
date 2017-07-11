<?php namespace App\Events\Frontend\System;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Queue\SerializesModels;

/**
 * Class Push.
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


    /**
     * Push constructor.
     * @param $user
     * @param $title; 通知标题
     * @param $body; 通知内容
     * @param array $payload; 给前端的参数,定义事件类型和事件id
     * @param array $content;
     * @param int $template_id;模板id,默认就可以
     */
    public function __construct($user, $title, $body, $payload=[], $content=[], $template_id = 1)
    {
        $this->user = $user;
        $this->content = $content;
        $payload['title'] = $title;
        $this->content['payload'] = $payload;
        $this->title = $title;
        $this->body = $body;
        $this->payload = $payload;
        $this->template_id = $template_id;
    }


}