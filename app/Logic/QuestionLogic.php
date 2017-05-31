<?php namespace App\Logic;
use App\Models\Question;

/**
 * @author: wanghui
 * @date: 2017/5/31 下午7:30
 * @email: wanghui@yonglibao.com
 */

class QuestionLogic {

    public static function slackMsg(Question $question){
        $fields[] = [
            'title' => 'tags',
            'value' => implode(',',$question->tags()->pluck('name')->toArray())
        ];
        $url = route('ask.question.detail',['id'=>$question->id]);
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'text' => $question->title,
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $question->user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext'],
                    'fields' => $fields
                ]
            );
    }


}