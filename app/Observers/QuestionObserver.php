<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\Question\InvitationOvertimeAlertSystem;
use App\Logic\QuestionLogic;
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
        QuestionLogic::slackMsg($question,null,'')
            ->send('用户['.$question->user->name.']新建了问题','#C0C0C0');
        $overtime = Setting()->get('alert_minute_operator_question_uninvite',10);
        dispatch((new InvitationOvertimeAlertSystem($question->id,$overtime))->delay(Carbon::now()->addMinutes($overtime)));
    }

}