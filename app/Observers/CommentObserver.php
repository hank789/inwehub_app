<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: hank.huiwang@gmail.com
 */

use App\Logic\QuillLogic;
use App\Logic\TaskLogic;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
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
        $members = [];
        $notify = true;
        switch ($comment->source_type) {
            case 'App\Models\Article':
                $notifyType = '活动';
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
                event(new CreditEvent($source->user_id,Credit::KEY_PRO_OPPORTUNITY_COMMENTED,Setting()->get('coins_'.Credit::KEY_PRO_OPPORTUNITY_COMMENTED),Setting()->get('credits_'.Credit::KEY_PRO_OPPORTUNITY_COMMENTED),$comment->id,'项目机遇被回复'));
                break;
            case 'App\Models\Answer':
                $notifyType = '回答';
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
                    event(new CreditEvent($source->user_id,Credit::KEY_ANSWER_COMMENT,Setting()->get('coins_'.Credit::KEY_ANSWER_COMMENT),Setting()->get('credits_'.Credit::KEY_ANSWER_COMMENT),$comment->id,'专业回答被回复'));

                } else {
                    event(new CreditEvent($source->user_id,Credit::KEY_COMMUNITY_ANSWER_COMMENT,Setting()->get('coins_'.Credit::KEY_COMMUNITY_ANSWER_COMMENT),Setting()->get('credits_'.Credit::KEY_COMMUNITY_ANSWER_COMMENT),$comment->id,'互动回答被回复'));
                    $feed_question_title = '互动回答';
                    $feed_type = Feed::FEED_TYPE_COMMENT_FREE_QUESTION;
                    $feed_url = '/askCommunity/interaction/'.$source->id;
                    $feed_answer_content = $source->getContentText();
                }
                break;
            case 'App\Models\Submission':
                //动态
                if (Redis::connection()->hget('user.'.$comment->user_id.'.data', 'commentsCount') <= 2) {
                    TaskLogic::finishTask('newbie_readhub_comment',0,'newbie_readhub_comment',[$comment->user_id]);
                }
                event(new CreditEvent($source->user_id,Credit::KEY_READHUB_SUBMISSION_COMMENT,Setting()->get('coins_'.Credit::KEY_READHUB_SUBMISSION_COMMENT),Setting()->get('credits_'.Credit::KEY_READHUB_SUBMISSION_COMMENT),$comment->id,'动态分享被回复'));

                //产生一条feed流
                $submission = $source;
                $submission->increment('comments_number');
                $submission->calculationRate();
                $submission_user = User::find($submission->user_id);
                $group = Group::find($submission->group_id);
                $is_official_reply = $comment->comment_type == Comment::COMMENT_TYPE_OFFICIAL;
                if ($submission->group_id && !$group->public) {
                    //私密圈子的分享只通知圈子内的人
                    $members = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('user_id')->toArray();
                }
                if ($submission->group_id) {
                    $fields[] = [
                        'title' => '圈子',
                        'value' => $group->name
                    ];
                }
                $fields[] = [
                    'title' => '标题',
                    'value' => strip_tags($submission->title)
                ];

                $user = $comment->user;
                $notifyUids = [];
                $notifyUrl = '/c/'.$submission->category_id.'/'.$submission->slug.'?comment='.$comment->id;
                $notifyType = $submission->type == 'link' ? '文章':'分享';
                if ($submission->type == 'review') {
                    $notifyUrl = '/dianping/comment/'.$submission->slug.'?comment='.$comment->id;
                    $notifyType = '点评';
                }
                $fields[] = [
                    'title' => '地址',
                    'value' => config('app.mobile_url').'#'.$notifyUrl
                ];
                if ($comment->parent_id > 0 && $comment->parent->user_id != $comment->user_id) {
                    $parent_comment = $comment->parent;
                    $notifyUids[$parent_comment->user_id] = $parent_comment->user_id;
                    $notifyUser = User::find($parent_comment->user_id);
                    $notifyUser->notify(new CommentReplied($parent_comment->user_id,
                        [
                            'url'    => $notifyUrl,
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
                            'url'    => $notifyUrl,
                            'name'   => $is_official_reply?'官方':$user->name,
                            'avatar' => $is_official_reply?'':$user->avatar,
                            'title'  => ($is_official_reply?'官方':$user->name).'回复了'.$notifyType,
                            'comment_id' => $comment->id,
                            'body'   => $comment->formatContent(),
                            'notification_type' => Notification::NOTIFICATION_TYPE_READ,
                            'extra_body' => '原文：'.$submission->formatTitle()
                        ]));
                }
                //通知专栏作者
                if ($submission->author_id && $submission->author_id != $comment->user_id && !isset($notifyUids[$submission->author_id])) {
                    $notifyUids[$submission->user_id] = $submission->author_id;
                    if ($members && !in_array($submission->author_id,$members)) continue;
                    $notifyUser = User::find($submission->author_id);
                    $notifyUser->notify(new SubmissionReplied($submission->author_id,
                        [
                            'url'    => $notifyUrl,
                            'name'   => $user->name,
                            'avatar' => $user->avatar,
                            'title'  => $user->name.'回复了'.$notifyType,
                            'comment_id' => $comment->id,
                            'body'   => $comment->formatContent(),
                            'notification_type' => Notification::NOTIFICATION_TYPE_READ,
                            'extra_body' => '原文：'.$submission->formatTitle()
                        ]));
                }
                break;
            case 'App\Models\Category':
                $notifyType = '专题:'.$source->name;
                $notify = false;
                break;
            default:
                return;
        }
        event(new CreditEvent($comment->user_id,Credit::KEY_NEW_COMMENT,Setting()->get('coins_'.Credit::KEY_NEW_COMMENT),Setting()->get('credits_'.Credit::KEY_NEW_COMMENT),$comment->id,'回复成功'));


        //@了某些人
        if ($notify && $comment->mentions) {
            foreach ($comment->mentions as $m_uid) {
                if (isset($notifyUids[$m_uid])) continue;
                if ($members && !in_array($m_uid,$members)) continue;
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
            )->send('用户'.$comment->user->id.'['.$comment->user->name.']评论了'.$notifyType);
    }


    public function deleting(Comment $comment){
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
            )->send('用户'.$comment->user->id.'['.$comment->user->name.']删除了评论');
    }

}