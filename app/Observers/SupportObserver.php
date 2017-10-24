<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Support;
use App\Notifications\NewSupport;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupportObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 监听点赞事件。
     *
     * @param  Support  $support
     * @return void
     */
    public function created(Support $support)
    {
        $source = $support->source;
        $fields = [];
        switch ($support->supportable_type) {
            case 'App\Models\Answer':
                $title = '回答';
                $fields[] = [
                    'title' => '回答内容',
                    'value' => $source->getContentText(),
                    'short' => false
                ];
                $fields[] = [
                    'title' => '问题地址',
                    'value' => route('ask.question.detail',['id'=>$source->question_id]),
                    'short' => false
                ];
                //通知，自己除外
                if ($source->user_id != $support->user_id) {
                    $source->user->notify(new NewSupport($source->user_id, $support));
                }
                break;
            default:
                return;
        }

        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'  => 'good',
                    'fields' => $fields
                ]
            )->send('用户'.$support->user->id.'['.$support->user->name.']赞了'.$title);
    }



}