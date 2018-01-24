<?php

namespace App\Events\Frontend\System;

/**
 * 用户积分
 */
class Credit
{

    public $user_id;

    public $action;

    public $coins;

    public $credits;

    public $source_id;

    public $source_subject;

    public $toSlack;


    /**
     * 修改用户积分
     * @param $user_id; 用户id
     * @param $action;  执行动作：提问、回答、发起文章
     * @param int $source_id; 源：问题id、回答id、文章id等
     * @param string $source_subject; 源主题：问题标题、文章标题等
     * @param int $coins;      金币数/财富值
     * @param int $credits;    经验值
     * @param bool $toSlack
     */
    public function __construct($user_id,$action,$coins = 0,$credits = 0,$source_id = 0 ,$source_subject = null,$toSlack = true)
    {
        $this->user_id = $user_id;
        $this->action = $action;
        $this->coins = $coins;
        $this->credits = $credits;
        $this->source_id = $source_id;
        $this->source_subject = $source_subject;
        $this->toSlack = $toSlack;

    }


}
