<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\Question\PromiseOvertime;
use App\Logic\QuestionLogic;
use App\Logic\QuillLogic;
use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionInvitation;
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
                $response_time = Carbon::createFromTimestamp(strtotime($answer->promise_time))->diffInMinutes(Carbon::createFromTimestamp(strtotime($answer->created_at)));

                $fields[] = [
                    'title' => '承诺时间',
                    'value' => $answer->promise_time
                ];
                $this->slackMsg($answer->question,$fields)
                    ->send('用户['.$answer->user->name.']承诺在'.$response_time.'分钟内回答该问题');
                //承诺告警
                $overtime = Setting()->get('alert_minute_expert_over_question_promise_time',10);
                $promise_datetime = Carbon::createFromTimestamp(strtotime($answer->promise_time))->subMinutes($overtime);
                dispatch((new PromiseOvertime($answer->id,$overtime))->delay($promise_datetime));
                break;
            case 0:
            case 1:
                //已回答
                $question_invitation = QuestionInvitation::where("user_id","=",$answer->user_id)->where("question_id","=",$answer->question_id)->first();
                $response_time = Carbon::createFromTimestamp(time())->diffInMinutes(Carbon::createFromTimestamp(strtotime($question_invitation->created_at)));
                $cost_time = Carbon::createFromTimestamp(time())->diffInMinutes(Carbon::createFromTimestamp(strtotime($answer->question->created_at)));

                $fields[] = [
                    'title' => '回答内容',
                    'value' => $answer->getContentHtml()
                ];

                if ($answer->promise_time){
                    $fields[] = [
                        'title' => '承诺时间',
                        'value' => $answer->promise_time,
                        'short' => true
                    ];
                }

                $fields[] = [
                    'title' => '响应时间',
                    'value' => $response_time.'分钟',
                    'short' => true
                ];

                $fields[] = [
                    'title' => '总耗时',
                    'value' => $cost_time.'分钟',
                    'short' => true
                ];

                $this->slackMsg($answer->question,$fields)
                    ->send('用户['.$answer->user->name.']回答了该问题');
                    break;
            case 2:
                //拒绝回答
                $fields[] = [
                    'title' => '拒绝回答',
                    'value' => $answer->content,
                    'short' => true
                ];
                $fields[] = [
                    'title' => '拒绝标签',
                    'value' => implode(',',$answer->tags()->pluck('name')->toArray()),
                    'short' => true
                ];
                $this->slackMsg($answer->question,$fields,'warning')
                    ->send('用户['.$answer->user->name.']拒绝回答该问题');
                break;
        }
    }

    public function updated(Answer $answer){
        $this->created($answer);
    }


    protected function slackMsg(Question $question,array $other_fields = null){
        return QuestionLogic::slackMsg($question,$other_fields);
    }

}