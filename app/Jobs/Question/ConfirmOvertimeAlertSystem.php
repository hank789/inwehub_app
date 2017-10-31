<?php namespace App\Jobs\Question;

use App\Events\Frontend\System\Push;
use App\Logic\QuestionLogic;
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
 * 检查邀请回答是否延期,通知系统
 * Class ConfirmOvertime
 * @package App\Jobs\Question
 */
class ConfirmOvertimeAlertSystem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    protected $question_id;

    protected $overtime;

    public function __construct($question_id,$overtime)
    {
        $this->question_id = $question_id;
        $this->overtime = $overtime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $question = Question::find($this->question_id);
        if($question->status == 2) {
            $fields[] = [
                'title' => 'tags',
                'value' => implode(',',$question->tags()->pluck('name')->toArray())
            ];
            QuestionLogic::slackMsg('问题超过'.$this->overtime.'分钟没有专家响应',$question,null,'warning');
        }
    }
}
