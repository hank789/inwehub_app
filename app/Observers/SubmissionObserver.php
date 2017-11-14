<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Readhub\Submission;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubmissionObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听问题创建的事件。
     *
     * @param  Submission  $submission
     * @return void
     */
    public function created(Submission $submission)
    {

        $slackFields = [];
        foreach ($submission->data as $field=>$value){
            if ($value){
                $slackFields[] = [
                    'title' => $field,
                    'value' => $value
                ];
            }
        }
        $user = User::find($submission->user_id);
        $url = config('app.readhub_url').'/c/'.$submission->category_id.'/'.$submission->slug;
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'text' => $submission->title,
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext'],
                    'color'     => 'good',
                    'fields' => $slackFields
                ]
            )->send('新文章提交');
    }



}