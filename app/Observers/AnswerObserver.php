<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Answer;
use App\Models\Question;
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
        $url = route('ask.question.detail',['id'=>$question->id]);
        return \Slack::to('#'.env('SLACK_ASK_CHANNEL','app_ask_activity'))
            ->disableMarkdown()
            ->attach(
                [
                    'text' => $question->title,
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $question->user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext']
                ]
            );
    }

}