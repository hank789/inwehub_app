<?php namespace App\Events\Frontend\Answer;
use Illuminate\Queue\SerializesModels;

/**
 * 自动匹配邀请回答
 */
class Feedback
{

    public $feedback_id;

    public function __construct($feedback_id)
    {
        $this->feedback_id = $feedback_id;
    }
}
