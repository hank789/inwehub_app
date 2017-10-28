<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Comment;
use App\Models\Feed\Feed;
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
                    'value' => route('ask.question.detail',['id'=>$source->question_id]),
                    'short' => false
                ];

                //通知，自己除外
                if ($source->user_id != $comment->user_id) {
                    $source->user->notify(new NewComment($source->user_id, $comment));
                }
                //产生一条feed
                $question = $source->question;
                if ($question->question_type == 1) {
                    $feed_question_title = '专业回答';
                    $feed_type = Feed::FEED_TYPE_COMMENT_PAY_QUESTION;
                    $feed_url = '/askCommunity/major/'.$source->question_id;
                    $feed_answer_content = '';
                } else {
                    $feed_question_title = '互动回答';
                    $feed_type = Feed::FEED_TYPE_COMMENT_FREE_QUESTION;
                    $feed_url = '/askCommunity/interaction/'.$source->id;
                    $feed_answer_content = $source->getContentText();
                }
                feed()
                    ->causedBy($comment->user)
                    ->performedOn($comment)
                    ->withProperties([
                        'comment_content' => $comment->content,
                        'answer_user_name' => $source->user->name,
                        'question_title'   => $question->title,
                        'answer_content'   => $feed_answer_content,
                        'feed_url'         => $feed_url
                    ])
                    ->log($comment->user->name.'评论了'.$feed_question_title, $feed_type);
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
            )->send('用户'.$comment->user->id.'['.$comment->user->name.']评论了'.$title);
    }



}