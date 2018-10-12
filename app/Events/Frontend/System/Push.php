<?php namespace App\Events\Frontend\System;
/**
 * @author: wanghui
 * @date: 2017/5/9 下午7:31
 * @email: hank.huiwang@gmail.com
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
     * @param $inAppTitle; app内提醒的标题，不传和$title一致
     * @param array $content;
     * @param int $template_id;模板id,默认就可以
     */
    public function __construct($user_id, $title, $body, $payload=[], $inAppTitle = '', $content=[], $template_id = 1)
    {
        $this->user_id = $user_id;
        $this->content = $content;
        $payload['title'] = $inAppTitle?:$title;
        $this->content['payload'] = $payload;
        $this->title = $title;
        $this->body = empty($body)?'点击查看':$body;
        $this->payload = $payload;
        $this->template_id = $template_id;
    }


}