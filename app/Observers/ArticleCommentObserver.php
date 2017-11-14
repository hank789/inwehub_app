<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\NotifyInwehub;
use App\Models\Readhub\Comment;
use App\Models\Readhub\Submission;
use App\Models\User;
use App\Notifications\Readhub\CommentReplied;
use App\Notifications\Readhub\SubmissionReplied;
use Illuminate\Contracts\Queue\ShouldQueue;

class ArticleCommentObserver implements ShouldQueue {

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

        $submission = Submission::find($comment->submission_id);
        $submission->increment('comments_number');

        $slackFields = [];
        foreach ($submission->data as $field=>$value){
            if ($value){
                $slackFields[] = [
                    'title' => $field,
                    'value' => $value
                ];
            }
        }
        $user = User::find($comment->user_id);
        if ($comment->parent_id > 0 && $comment->parent_id != $comment->user_id) {
            $parent_comment = Comment::find($comment->parent_id);
            $notifyUser = User::find($parent_comment->user_id);
            $notifyUser->notify(new CommentReplied($parent_comment->user_id,
                [
                    'url'    => '/c/'.$submission->category_id.'/'.$submission->slug.'?comment='.$comment->id,
                    'name'   => $user->name,
                    'avatar' => $user->avatar,
                    'title'  => $user->name.'回复了你的评论',
                    'submission_title' => $submission->title,
                    'comment_id' => $comment->id,
                    'body'   => $comment->body,
                    'extra_body' => '原回复：'.$parent_comment->body
                ]));
        } elseif ($submission->user_id != $comment->user_id) {
            $notifyUser = User::find($submission->user_id);
            $notifyUser->notify(new SubmissionReplied($submission->user_id,
                [
                    'url'    => '/c/'.$submission->category_id.'/'.$submission->slug.'?comment='.$comment->id,
                    'name'   => $user->name,
                    'avatar' => $user->avatar,
                    'title'  => $user->name.'回复了文章',
                    'comment_id' => $comment->id,
                    'body'   => $comment->body,
                    'extra_body' => '原文：'.$submission->title
                ]));
        }

        $this->handleMentions($comment, $submission);


        $url = config('app.readhub_url').'/c/'.$submission->category_id.'/'.$submission->slug;
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'text' => $comment->body,
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext'],
                    'color'     => 'good',
                    'fields' => $slackFields
                ]
            )->send('文章有新的评论');
    }


    protected function handleMentions($comment, $submission)
    {
        if (!preg_match_all('/@([\S]+)/', $comment->body, $mentionedUsernames)) {
            return;
        }

        foreach ($mentionedUsernames[1] as $key => $username) {
            // set a limit so they can't just mention the whole website! lol
            if ($key === 5) {
                return;
            }

            if ($user = User::where('name',$username)->first()) {
                //$user->notify(new UsernameMentioned($submission,$comment));
            }
        }
    }



}