<?php namespace App\Jobs\Question;

use App\Events\Frontend\System\Push;
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
class ConfirmOvertime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    protected $question_id;
    protected $invitation_id;


    public function __construct($question_id, $invitation_id)
    {
        $this->question_id = $question_id;
        $this->invitation_id = $invitation_id;
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
            $question_invitation = QuestionInvitation::find($this->invitation_id);
            if ($question_invitation->status == 0) {
                event(new Push($question_invitation->user,'请您尽快确认回答邀请',$question->title,['object_type'=>'answer','object_id'=>$question->id]));
            }
        }
    }
}
