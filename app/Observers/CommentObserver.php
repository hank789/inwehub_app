<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Comment;
use App\Notifications\NewComment;
use Illuminate\Contracts\Queue\ShouldQueue;

class CommentObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 监听问题创建的事件。
     *
     * @param  Comment  $comment
     * @return void
     */
    public function created(Comment $comment)
    {
        $source = $comment->source;
        switch ($comment->source_type) {
            case 'App\Models\Article':
                $title = '活动';
                $fields[] = [
                    'title' => '活动标题',
                    'value' => $source->title,
                    'short' => false
                ];
                $fields[] = [
                    'title' => '活动地址',
                    'value' => route('blog.article.detail',['id'=>$source->id]),
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
                    'value' => route('ask.question.detail',['id'=>$source->question->id]),
                    'short' => false
                ];
                //通知
                $source->user->notify(new NewComment($source->user_id, $comment));
                break;
            default:
                return;
        }

        $fields[] = [
            'title' => '评论内容',
            'value' => $comment->content,
            'short' => false
        ];
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'  => 'good',
                    'fields' => $fields
                ]
            )->send('用户['.$comment->user->name.']评论了'.$title);
    }



}