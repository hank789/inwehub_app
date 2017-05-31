<?php namespace App\Listeners\Frontend\Question;
use App\Events\Frontend\Question\AutoInvitation;
use App\Events\Frontend\System\Push;
use App\Logic\TaskLogic;
use App\Models\QuestionInvitation;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
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
        $userTags = UserTag::leftJoin('user_data','user_tags.user_id','=','user_data.user_id')->where('user_data.authentication_status',1)->whereIn('user_tags.tag_id',$tagIds)->where('user_tags.skills','>=','1')->pluck('user_tags.user_id')->toArray();
        $userTags = array_unique($userTags);
        foreach($userTags as $uid){
            $toUser = User::find($uid);
            $invitation = QuestionInvitation::where('user_id',$uid)->where('from_user_id',$question->user_id)->where('question_id',$question->id)->first();
            if(empty($invitation)){
                $invitation = QuestionInvitation::create([
                    'from_user_id'=> $question->user_id,
                    'question_id'=> $question->id,
                    'user_id'=> $uid,
                    'send_to'=> 'auto' //标示自动匹配
                ]);
            }

            //已邀请
            $question->invitedAnswer();
            //记录动态
            TaskLogic::doing($question->user_id,'question_invite_answer_confirming',get_class($question),$question->id,$question->title,'');
            //记录任务
            TaskLogic::task($uid,get_class($question),$question->id,Task::ACTION_TYPE_ANSWER);
            //推送
            event(new Push($toUser,'您有新的回答邀请',$question->title,['object_type'=>'answer','object_id'=>$question->id]));
        }
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
