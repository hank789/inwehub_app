<?php

namespace App\Events\Frontend\Question;
use Illuminate\Queue\SerializesModels;

/**
 * 自动匹配邀请回答
 */
class AutoInvitation
{
    use SerializesModels;

    public $question;

    public function __construct($question)
    {
        $this->question = $question;
    }
}
