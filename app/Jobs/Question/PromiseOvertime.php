<?php namespace App\Jobs\Question;

use App\Events\Frontend\System\Push;
use App\Logic\WechatNotice;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Flc\Alidayu\App;
use Flc\Alidayu\Client;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 检查邀请回答是否延期
 * Class ConfirmOvertime
 * @package App\Jobs\Question
 */
class PromiseOvertime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    protected $answer_id;

    protected $overtime;


    public function __construct($answer_id,$overtime)
    {
        $this->answer_id = $answer_id;
        $this->overtime = $overtime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $answer = Answer::find($this->answer_id);
        if($answer->status == 3) {
            $question = $answer->question;
            event(new Push($answer->user_id,'距离您的承诺时间还有'.$this->overtime.'分钟',$question->title,['object_type'=>'answer','object_id'=>$question->id]));
            WechatNotice::newTaskNotice($answer->user_id,$question->title,'question_answer_promise_overtime',$answer);
        }
    }
}
