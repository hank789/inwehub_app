<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: hank.huiwang@gmail.com
 */

use App\Jobs\Question\InvitationOvertimeAlertSystem;
use App\Logic\QuestionLogic;
use App\Models\Feed\Feed;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuestionObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听问题创建的事件。
     *
     * @param  Question  $question
     * @return void
     */
    public function created(Question $question)
    {

        QuestionLogic::slackMsg('用户'.$question->user->id.'['.$question->user->name.']新建了问题',$question,null,'#C0C0C0');
        $overtime = Setting()->get('alert_minute_operator_question_uninvite',10);
        if ($question->question_type == 1) {
            dispatch((new InvitationOvertimeAlertSystem($question->id,$overtime))->delay(Carbon::now()->addMinutes($overtime)));
        }
        $question->setKeywordTags();
        $question->getRelatedProducts();

        if ($question->question_type == 2 && $question->hide == 0) {
            //关注提问者的用户通知
            /*$attention_users = Attention::where('source_type','=',get_class($question->user))->where('source_id','=',$question->user_id)->pluck('user_id')->toArray();
            unset($attention_users[$question->user_id]);
            foreach ($attention_users as $attention_uid) {
                $attention_user = User::find($attention_uid);
                $attention_user->notify(new FollowedUserAsked($attention_uid,$question));
            }*/
        }
        //只有互动问答才产生一条feed流
        if ($question->question_type == 2) {
            $feed_question_title = '问答';
            $feed_type = Feed::FEED_TYPE_CREATE_FREE_QUESTION;
            feed()
                ->causedBy($question->user)
                ->performedOn($question)
                ->anonymous($question->hide)
                ->withProperties(['question_title'=>$question->title])
                ->log(($question->hide ? '匿名':$question->user->name).'发布了'.$feed_question_title, $feed_type);
        }
    }

}