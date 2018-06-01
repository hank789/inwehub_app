<?php namespace App\Jobs\Question;

use App\Logic\QuestionLogic;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionInvitation;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class ConfirmOvertime
 * @package App\Jobs\Question
 */
class AutoSecondInvation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    protected $question_id;

    public function __construct($question_id)
    {
        $this->question_id = $question_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $question = Question::find($this->question_id);
        if ($question->status >= 8 ) return;
        if (Carbon::now()->diffInHours($question->created_at) >= 48) return;
        if (Carbon::now()->hour >= 23) {
            dispatch((new AutoSecondInvation($question->id))->delay(Carbon::tomorrow()->addHours(10)));
            return;
        } elseif (Carbon::now()->hour <= 4) {
            dispatch((new AutoSecondInvation($question->id))->delay(Carbon::today()->addHours(10)));
            return;
        }
        $tagIds = $question->tags()->pluck('tags.id')->toArray();
        $userTags = UserTag::whereIn('tag_id',$tagIds)->where('views','>=','1')->pluck('user_id')->toArray();
        $userTags = array_merge($userTags,UserTag::whereIn('tag_id',$tagIds)->where('articles','>=','1')->pluck('user_id')->toArray());
        $userTags = array_unique($userTags);
        $fields = [];
        foreach($userTags as $uid){
            if($uid == $question->user_id) continue;
            $invitation = QuestionInvitation::where('user_id',$uid)->where('from_user_id',$question->user_id)->where('question_id',$question->id)->first();
            if ($invitation) continue;
            $answer = Answer::where('question_id',$question->id)->where('user_id',$uid)->first();
            if ($answer) continue;
            $invitation = QuestionInvitation::create([
                'from_user_id'=> $question->user_id,
                'question_id'=> $question->id,
                'user_id'=> $uid,
                'send_to'=> 'auto' //标示自动匹配
            ]);
            $user = User::find($uid);
            $notifyLimit = RateLimiter::instance()->getValue('notify_user',$uid);
            if ($notifyLimit) {
                $user->notify((new NewQuestionInvitation($uid, $question,$question->user_id,$invitation->id,false))->delay(Carbon::now()->addMinutes($notifyLimit * 5)));
            } else {
                $user->notify((new NewQuestionInvitation($uid, $question,$question->user_id,$invitation->id,false)));
            }
            $fields[] = [
                'title' => '邀请回答者',
                'value' => $user->id.'['.$user->name.']'
            ];
        }
        QuestionLogic::slackMsg('[系统]自动邀请相关人员参与悬赏问题',$question,$fields);
    }
}
