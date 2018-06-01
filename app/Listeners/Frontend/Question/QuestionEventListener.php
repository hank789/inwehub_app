<?php namespace App\Listeners\Frontend\Question;
use App\Events\Frontend\Question\AutoInvitation;
use App\Jobs\Question\AutoSecondInvation;
use App\Logic\QuestionLogic;
use App\Models\QuestionInvitation;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionInvitation;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;


class QuestionEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param AutoInvitation $event
     */
    public function autoInvitation($event)
    {
        $question = $event->question;
        $tagIds = $question->tags()->pluck('tags.id')->toArray();
        $userTags = UserTag::whereIn('tag_id',$tagIds)->where('skills','>=',1)->pluck('user_id')->toArray();
        $userTags = array_merge($userTags,UserTag::whereIn('tag_id',$tagIds)->where('answers','>=',1)->pluck('user_id')->toArray());
        $userTags = array_merge($userTags,UserTag::whereIn('tag_id',$tagIds)->where('adoptions','>=',1)->pluck('user_id')->toArray());
        $userTags = array_unique($userTags);
        $fields = [];
        foreach($userTags as $uid){
            if($uid == $question->user_id) continue;
            $invitation = QuestionInvitation::where('user_id',$uid)->where('from_user_id',$question->user_id)->where('question_id',$question->id)->first();
            if ($invitation) continue;
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
                $user->notify(new NewQuestionInvitation($uid, $question,$question->user_id,$invitation->id,false));
            }
            $fields[] = [
                'title' => '邀请回答者',
                'value' => $user->id.'['.$user->name.']'
            ];
        }
        dispatch((new AutoSecondInvation($question->id))->delay(Carbon::now()->addHours(3)));
        QuestionLogic::slackMsg('[系统]自动邀请相关人员参与悬赏问题',$question,$fields);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            AutoInvitation::class,
            'App\Listeners\Frontend\Question\QuestionEventListener@autoInvitation'
        );
    }
}
