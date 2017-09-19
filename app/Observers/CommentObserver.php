<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Comment;
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
        $article = $comment->source()->first();
        $fields[] = [
            'title' => '活动标题',
            'value' => $article->title,
            'short' => false
        ];
        $fields[] = [
            'title' => '活动地址',
            'value' => route('blog.article.detail',['id'=>$article->id]),
            'short' => false
        ];
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
            )->send('用户['.$comment->user->name.']评论了活动');
    }



}