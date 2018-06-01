<?php namespace App\Jobs\Question;

use App\Events\Frontend\System\Push;
use App\Models\Question;
use App\Models\QuestionInvitation;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        if($question->status < 6) {
            $question_invitation = QuestionInvitation::find($this->invitation_id);
            if ($question_invitation->status == 0) {
                if (Carbon::now()->diffInHours($question->created_at) >= 48) return;
                if (Carbon::now()->hour >= 23) {
                    dispatch((new ConfirmOvertime($this->question_id,$this->invitation_id))->delay(Carbon::tomorrow()->addHours(10)));
                } elseif (Carbon::now()->hour <= 4) {
                    dispatch((new ConfirmOvertime($this->question_id,$this->invitation_id))->delay(Carbon::today()->addHours(10)));
                    return;
                } elseif (Carbon::now()->diffInHours($question->created_at) >= 12) {
                    dispatch((new ConfirmOvertime($this->question_id,$this->invitation_id))->delay(Carbon::now()->addHours(24)));
                } elseif ((Carbon::now()->diffInHours($question->created_at) <= 3)) {
                    dispatch((new ConfirmOvertime($this->question_id,$this->invitation_id))->delay(Carbon::now()->addHours(3)));
                } elseif ((Carbon::now()->diffInHours($question->created_at) <= 5)) {
                    dispatch((new ConfirmOvertime($this->question_id,$this->invitation_id))->delay(Carbon::now()->addHours(9)));
                }
                event(new Push($question_invitation->user_id,'您的朋友还在等着您答疑解惑呢',$question->title,['object_type'=>'answer','object_id'=>$question->id]));
            }
        }
    }
}
