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
use App\Models\Attention;
use App\Models\Feed\Feed;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\User;
use App\Notifications\FollowedQuestionAnswered;
use App\Notifications\FollowedUserAnswered;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnswerObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function created(Answer $answer, $update = false)
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

                $fields[] = [
                    'title' => '回答内容',
                    'value' => $answer->getContentText()
                ];
                if ($answer->promise_time){
                    $fields[] = [
                        'title' => '承诺时间',
                        'value' => $answer->promise_time,
                        'short' => true
                    ];
                }

                if ($question_invitation) {
                    $response_time = Carbon::createFromTimestamp(time())->diffInMinutes(Carbon::createFromTimestamp(strtotime($question_invitation->created_at)));
                    $fields[] = [
                        'title' => '响应时间',
                        'value' => $response_time.'分钟',
                        'short' => true
                    ];
                    $cost_time = Carbon::createFromTimestamp(time())->diffInMinutes(Carbon::createFromTimestamp(strtotime($answer->question->created_at)));

                    $fields[] = [
                        'title' => '总耗时',
                        'value' => $cost_time.'分钟',
                        'short' => true
                    ];
                }

                if ($answer->status == Answer::ANSWER_STATUS_FINISH) {
                    if (($update && $answer->question->question_type == 2) || ($update == false && $answer->question->question_type == 1 )) {
                        //互动问答修改和专业问答承诺回答时不通知
                    } else {
                        //关注问题的用户接收通知
                        $attention_questions = Attention::where('source_type','=',get_class($answer->question))->where('source_id','=',$answer->question->id)->get();
                        //关注回答者的用户接收通知
                        $attention_users = Attention::where('source_type','=',get_class($answer->user))->where('source_id','=',$answer->user->id)->pluck('user_id')->toArray();

                        foreach ($attention_questions as $attention_question) {
                            //去除重复通知
                            unset($attention_users[$attention_question->user_id]);
                            if ($attention_question->user_id == $answer->question->user_id || $attention_question->user_id == $answer->user_id) continue;
                            $attention_question->user->notify(new FollowedQuestionAnswered($attention_question->user_id,$answer->question,$answer));
                        }
                        foreach ($attention_users as $attention_uid) {
                            $attention_user = User::find($attention_uid);
                            $attention_user->notify(new FollowedUserAnswered($attention_uid,$answer->question,$answer));
                        }
                        //产生一条feed流
                        if ($answer->question->question_type == 1) {
                            $feed_question_title = '专业问答';
                            $feed_type = Feed::FEED_TYPE_ANSWER_PAY_QUESTION;
                        } else {
                            $feed_question_title = '互动问答';
                            $feed_type = Feed::FEED_TYPE_ANSWER_FREE_QUESTION;
                        }
                        feed()
                            ->causedBy($answer->user)
                            ->performedOn($answer)
                            ->withProperties(['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'question_title'=>$answer->question->title,'answer_content'=>$answer->getContentText()])
                            ->log($answer->user->name.'回答了'.$feed_question_title, $feed_type);
                    }
                }

                $this->slackMsg($answer->question,$fields)
                    ->send('用户'.$answer->user->id.'['.$answer->user->name.']回答了该问题');
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
                    ->send('用户'.$answer->user->id.'['.$answer->user->name.']拒绝回答该问题');
                break;
        }
    }

    public function updated(Answer $answer){
        $this->created($answer, true);
    }


    protected function slackMsg(Question $question,array $other_fields = null){
        return QuestionLogic::slackMsg($question,$other_fields);
    }

}