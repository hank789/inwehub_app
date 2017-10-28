<?php

namespace App\Jobs;

use App\Logic\TaskLogic;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Readhub\Comment;
use App\Models\Readhub\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Notification as NotificationModel;
use App\Events\Frontend\System\Credit as CreditEvent;
use Illuminate\Support\Facades\Redis;


class NotifyInwehub implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $message;
    public $type;
    public $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $type, $message)
    {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->user_id);
        $class = $this->type;
        switch ($class){
            case 'NewComment':
                event(new CreditEvent($this->user_id,Credit::KEY_READHUB_NEW_COMMENT,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_COMMENT),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_COMMENT),$this->message['commnet_id'],''));
                if (Redis::connection()->hget('user.'.$this->user_id.'.data', 'commentsCount') <= 2) {
                    TaskLogic::finishTask('newbie_readhub_comment',0,'newbie_readhub_comment',[$this->user_id]);
                }
                //产生一条feed流
                $comment = Comment::find($this->message['commnet_id']);
                $submission = Submission::find($comment->submission_id);
                $submission_user = User::find($submission->user_id);
                feed()
                    ->causedBy($user)
                    ->performedOn($comment)
                    ->withProperties([
                        'comment_id'=>$comment->id,
                        'category_id'=>$submission->category_id,
                        'slug'=>$submission->slug,
                        'submission_title'=>$submission->title,
                        'domain'=>$submission->data['domain'],
                        'img'=>$submission->data['img'],
                        'submission_username' => $submission_user->name,
                        'comment_content' => $comment->body
                    ])
                    ->log($user->name.'评论了文章', Feed::FEED_TYPE_COMMENT_READHUB_ARTICLE);
                return;
                break;
            case 'NewSubmission':
                event(new CreditEvent($this->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$this->message['submission_id'],''));
                //产生一条feed流
                $submission = Submission::find($this->message['submission_id']);
                feed()
                    ->causedBy($user)
                    ->performedOn($submission)
                    ->withProperties(['view_url'=>$submission->data['url'],'category_id'=>$submission->category_id,'slug'=>$submission->slug,'submission_title'=>$submission->title,'domain'=>$submission->data['domain'],'img'=>$submission->data['img']])
                    ->log($user->name.'发布了文章', Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);
                return;
                break;
            case 'NewSubmissionUpVote':
                //文章点赞
                //产生一条feed流
                $submission = Submission::find($this->message['submission_id']);
                $submission_user = User::find($submission->user_id);
                feed()
                    ->causedBy($user)
                    ->performedOn($submission)
                    ->withProperties(['view_url'=>$submission->data['url'],'submission_username' => $submission_user->name,'category_id'=>$submission->category_id,'slug'=>$submission->slug,'submission_title'=>$submission->title,'domain'=>$submission->data['domain'],'img'=>$submission->data['img']])
                    ->log($user->name.'赞了文章', Feed::FEED_TYPE_UPVOTE_READHUB_ARTICLE);
                return;
                break;
        }
        $this->message['notification_type'] = NotificationModel::NOTIFICATION_TYPE_READ;
        if (class_exists($class)) {
            $user->notify(new $class($this->user_id,$this->message));
        } else {
            app('sentry')->captureException(new \Exception('class:'.$class.'不存在'));
        }
    }
}
