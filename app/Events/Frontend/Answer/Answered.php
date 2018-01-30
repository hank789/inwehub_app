<?php namespace App\Events\Frontend\Answer;
use App\Models\Answer;
use Illuminate\Queue\SerializesModels;

/**
 * 自动匹配邀请回答
 */
class Answered
{
    use SerializesModels;

    public $answer;

    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }
}
