<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\Question\InvitationOvertimeAlertSystem;
use App\Logic\QuestionLogic;
use App\Models\Attention;
use App\Models\Question;
use App\Models\User;
use App\Notifications\FollowedUserAsked;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuestionObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 监听问题创建的事件。
     *
     * @param  Question  $question
     * @return void
     */
    public function created(Question $question)
    {

        QuestionLogic::slackMsg($question,null,'')
            ->send('用户['.$question->user->name.']新建了问题','#C0C0C0');
        $overtime = Setting()->get('alert_minute_operator_question_uninvite',10);
        if ($question->question_type == 1) {
            dispatch((new InvitationOvertimeAlertSystem($question->id,$overtime))->delay(Carbon::now()->addMinutes($overtime)));
        }
        if ($question->question_type == 2 && $question->hide == 0) {
            //关注提问者的用户通知
            $attention_users = Attention::where('source_type','=',get_class($question->user))->where('source_id','=',$question->user_id)->pluck('user_id')->toArray();
            unset($attention_users[$question->user_id]);
            foreach ($attention_users as $attention_uid) {
                $attention_user = User::find($attention_uid);
                $attention_user->notify(new FollowedUserAsked($attention_uid,$question));
            }
        }
    }

}