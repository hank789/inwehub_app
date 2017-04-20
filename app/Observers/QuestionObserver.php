<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Question;
use App\Models\QuestionInvitation;
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
        $this->slackMsg($question)
            ->send('用户['.$question->user->name.']新建了问题');
    }

    public function updated(Question $question){
        switch($question->status){
            case 2:
                $question_invitation = QuestionInvitation::where('question_id',$question->id)->whereIn('status',[0,1])->first();
                $this->slackMsg($question)
                    ->send('问题分配给了用户:'.$question_invitation->user->name);
                break;
        }
    }


    protected function slackMsg(Question $question){
        $url = route('ask.question.detail',['id'=>$question->id]);
        return \Slack::to('#app_ask_activity')
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