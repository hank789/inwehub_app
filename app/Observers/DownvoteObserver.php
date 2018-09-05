<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: hank.huiwang@gmail.com
 */
use App\Models\DownVote;
use App\Services\RateLimiter;
use Illuminate\Contracts\Queue\ShouldQueue;

class DownvoteObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听点赞事件。
     *
     * @param  DownVote  $support
     * @return void
     */
    public function created(DownVote $downVote)
    {
        $source = $downVote->source;
        $fields = [];
        $title = '';
        if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('downvote:'.get_class($source),$source->id.'_'.$downVote->user_id,0)) {
            switch ($downVote->source_type) {
                case 'App\Models\Comment':
                    $title = '评论';
                    $answer = $source->source;
                    $fields[] = [
                        'title' => '评论内容',
                        'value' => $source->content,
                        'short' => false
                    ];
                    $fields[] = [
                        'title' => '回答内容',
                        'value' => $answer->getContentText(),
                        'short' => false
                    ];
                    $fields[] = [
                        'title' => '问题地址',
                        'value' => route('ask.question.detail',['id'=>$answer->question_id]),
                        'short' => false
                    ];
                    break;
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
                    break;
                case 'App\Models\Submission':
                    $title = '动态';
                    $fields[] = [
                        'title' => '标题',
                        'value' => $source->formatTitle()
                    ];
                    $fields[] = [
                        'title' => '地址',
                        'value' => config('app.mobile_url').'#/c/'.$source->category_id.'/'.$source->slug
                    ];
                    foreach ($source->data as $field=>$value){
                        if ($value){
                            if (is_array($value)) {
                                foreach ($value as $key => $item) {
                                    $fields[] = [
                                        'title' => $field.$key,
                                        'value' => $item
                                    ];
                                }
                            } else {
                                $fields[] = [
                                    'title' => $field,
                                    'value' => $value
                                ];
                            }
                        }
                    }

                    break;
                default:
                    return;
                    break;
            }
        }
        if ($fields) {
            \Slack::to(config('slack.ask_activity_channel'))
                ->disableMarkdown()
                ->attach(
                    [
                        'color'  => 'good',
                        'fields' => $fields
                    ]
                )->send('用户'.$downVote->user->id.'['.$downVote->user->name.']踩了'.$title);
        }
        return;
    }



}