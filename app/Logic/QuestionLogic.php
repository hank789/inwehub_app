<?php namespace App\Logic;
use App\Jobs\UpdateQuestionRate;
use App\Models\Answer;
use App\Models\Question;
use App\Services\RateLimiter;
use Carbon\Carbon;

/**
 * @author: wanghui
 * @date: 2017/5/31 下午7:30
 * @email: wanghui@yonglibao.com
 */

class QuestionLogic {

    public static function slackMsg($title, Question $question, array $other_fields = null, $color = 'good'){
        try{
            $fields[] = [
                'title' => '标签',
                'value' => implode(',',$question->tags()->pluck('name')->toArray())
            ];
            $fields[] = [
                'title' => '类型',
                'value' => '问答'
            ];
            if($other_fields){
                $fields = array_merge($fields,$other_fields);
            }
            $url = route('ask.question.detail',['id'=>$question->id]);
            return \Slack::to(config('slack.ask_activity_channel'))
                ->disableMarkdown()
                ->attach(
                    [
                        'text' => $question->title,
                        'pretext' => '[问题链接]('.$url.')',
                        'author_name' => $question->user->name,
                        'author_link' => $url,
                        'mrkdwn_in' => ['pretext'],
                        'color'     => $color,
                        'fields' => $fields
                    ]
                )->send($title);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
        return true;
    }

    public static function calculationQuestionRate($questionId){
        $event = 'calculation:question:rate';
        $limit = RateLimiter::instance()->getValue($event,$questionId);
        if (!$limit) {
            RateLimiter::instance()->increase($event,$questionId,300,1);
            dispatch(new UpdateQuestionRate($questionId))->delay(Carbon::now()->addMinutes(5));
        }
    }


}