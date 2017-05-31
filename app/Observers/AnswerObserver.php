<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\Question\PromiseOvertime;
use App\Logic\QuestionLogic;
use App\Models\Answer;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnswerObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function created(Answer $answer)
    {
        switch($answer->status){
            case 3:
                //承诺回答
                $this->slackMsg($answer->question)
                    ->send('用户['.$answer->user->name.']承诺在'.$answer->promise_time.'前回答该问题');
                //承诺告警
                $overtime = Setting()->get('alert_minute_expert_over_question_promise_time',10);
                $promise_datetime = Carbon::createFromTimestamp(strtotime($answer->promise_time))->subMinutes($overtime);
                dispatch((new PromiseOvertime($answer->id,$overtime))->delay($promise_datetime));
                break;
            case 0:
            case 1:
                //已回答
            $this->slackMsg($answer->question)
                ->send('用户['.$answer->user->name.']回答了该问题'.($answer->promise_time?',承诺时间是:'.$answer->promise_time:''));
                break;
            case 2:
                //拒绝回答
                $this->slackMsg($answer->question)
                    ->send('用户['.$answer->user->name.']拒绝回答该问题');
                break;
        }
    }

    public function updated(Answer $answer){
        switch($answer->status){
            case 1:
                $this->slackMsg($answer->question)
                    ->send('用户['.$answer->user->name.']回答了该问题'.($answer->promise_time?',承诺时间是:'.$answer->promise_time:''));
                break;
        }
    }


    protected function slackMsg(Question $question){
        return QuestionLogic::slackMsg($question);
    }

}