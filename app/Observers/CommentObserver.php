<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Logic\TaskLogic;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Notification;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\NewComment;
use App\Notifications\Readhub\CommentReplied;
use App\Notifications\Readhub\SubmissionReplied;
use App\Notifications\UserCommentMentioned;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Frontend\System\Credit as CreditEvent;
use Illuminate\Support\Facades\Redis;


class CommentObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

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

                $notifyUids = [];
                //回复了回复的回复
                if ($comment->parent_id > 0 && $comment->parent->user_id != $comment->user_id) {
                    $parent_comment = $comment->parent;
                    $notifyUids[$parent_comment->user_id] = $parent_comment->user_id;
                    $notifyUser = User::find($parent_comment->user_id);
                    $question = Question::find($source->question_id);
                    switch ($question->question_type){
                        case 1:
                            $url = '/askCommunity/major/'.$source->question_id;
                            break;
                        case 2:
                            $url = '/askCommunity/interaction/'.$source->id;
                            break;
                    }
                    $notifyUser->notify(new CommentReplied($parent_comment->user_id,
                        [
                            'url'    => $url,
                            'name'   => $comment->user->name,
                            'avatar' => $comment->user->avatar,
                            'title'  => $comment->user->name.'回复了你的评论',
                            'submission_title' => strip_tags($question->title),
                            'comment_id' => $comment->id,
                            'body'   => $comment->formatContent(),
                            'notification_type' => Notification::NOTIFICATION_TYPE_TASK,
                            'extra_body' => '原回复：'.$parent_comment->formatContent()
                        ]));
                }
                //通知，自己除外
                if ($source->user_id != $comment->user_id && !isset($notifyUids[$source->user_id])) {
                    $notifyUids[$source->user_id] = $source->user_id;
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
                }
                break;
            case 'App\Models\Submission':
                //动态
                $title = '动态';
                event(new CreditEvent($comment->user_id,Credit::KEY_READHUB_NEW_COMMENT,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_COMMENT),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_COMMENT),$comment->id,''));
                if (Redis::connection()->hget('user.'.$comment->user_id.'.data', 'commentsCount') <= 2) {
                    TaskLogic::finishTask('newbie_readhub_comment',0,'newbie_readhub_comment',[$comment->user_id]);
                }
                //产生一条feed流
                $submission = Submission::find($comment->source_id);
                $submission->increment('comments_number');
                $submission_user = User::find($submission->user_id);
                if ($submission->type == 'link' && false) {
                    //评论的feed不产生，全部在发布文章上聚合显示
                    feed()
                        ->causedBy($comment->user)
                        ->performedOn($comment)
                        ->withProperties([
                            'comment_id'=>$comment->id,
                            'category_id'=>$submission->category_id,
                            'slug'=>$submission->slug,
                            'submission_title'=>$submission->title,
                            'type' => $submission->type,
                            'domain'=>$submission->data['domain']??'',
                            'img'=>$submission->data['img']??'',
                            'submission_username' => $submission_user->name,
                            'comment_content' => $comment->content
                        ])
                        ->log($comment->user->name.'评论了文章', Feed::FEED_TYPE_COMMENT_READHUB_ARTICLE);
                }

                $fields[] = [
                    'title' => '标题',
                    'value' => strip_tags($submission->title)
                ];
                $fields[] = [
                    'title' => '地址',
                    'value' => config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug
                ];
                foreach ($submission->data as $field=>$value){
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
                $user = $comment->user;
                $notifyUids = [];
                if ($comment->parent_id > 0 && $comment->parent->user_id != $comment->user_id) {
                    $parent_comment = $comment->parent;
                    $notifyUids[$parent_comment->user_id] = $parent_comment->user_id;
                    $notifyUser = User::find($parent_comment->user_id);
                    $notifyUser->notify(new CommentReplied($parent_comment->user_id,
                        [
                            'url'    => '/c/'.$submission->category_id.'/'.$submission->slug.'?comment='.$comment->id,
                            'name'   => $user->name,
                            'avatar' => $user->avatar,
                            'title'  => $user->name.'回复了你的评论',
                            'submission_title' => $submission->formatTitle(),
                            'comment_id' => $comment->id,
                            'body'   => $comment->formatContent(),
                            'notification_type' => Notification::NOTIFICATION_TYPE_READ,
                            'extra_body' => '原回复：'.$parent_comment->formatContent()
                        ]));
                }
                if ($submission->user_id != $comment->user_id && !isset($notifyUids[$submission->user_id])) {
                    $notifyUids[$submission->user_id] = $submission->user_id;
                    $notifyUser = User::find($submission->user_id);
                    $notifyUser->notify(new SubmissionReplied($submission->user_id,
                        [
                            'url'    => '/c/'.$submission->category_id.'/'.$submission->slug.'?comment='.$comment->id,
                            'name'   => $user->name,
                            'avatar' => $user->avatar,
                            'title'  => $user->name.'回复了'.($submission->type == 'link' ? '文章':'动态'),
                            'comment_id' => $comment->id,
                            'body'   => $comment->formatContent(),
                            'notification_type' => Notification::NOTIFICATION_TYPE_READ,
                            'extra_body' => '原文：'.$submission->formatTitle()
                        ]));
                }
                break;
            default:
                return;
        }

        //@了某些人
        if ($comment->mentions) {
            foreach ($comment->mentions as $m_uid) {
                if (isset($notifyUids[$m_uid])) continue;
                $mUser = User::find($m_uid);
                $mUser->notify(new UserCommentMentioned($m_uid,$comment));
            }
        }

        $fields[] = [
            'title' => '评论内容',
            'value' => $comment->formatContent(),
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