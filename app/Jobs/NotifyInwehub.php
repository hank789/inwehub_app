<?php

namespace App\Jobs;

use App\Logic\TaskLogic;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Submission;
use App\Models\User;
use App\Services\RateLimiter;
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
                return;
                event(new CreditEvent($this->user_id,Credit::KEY_READHUB_NEW_COMMENT,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_COMMENT),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_COMMENT),$this->message['commnet_id'],''));
                if (Redis::connection()->hget('user.'.$this->user_id.'.data', 'commentsCount') <= 2) {
                    TaskLogic::finishTask('newbie_readhub_comment',0,'newbie_readhub_comment',[$this->user_id]);
                }
                //产生一条feed流
                $comment = Comment::find($this->message['commnet_id']);
                //同步评论
                \App\Models\Comment::create(
                    [
                        'user_id'     => $comment->user_id,
                        'content'     => $comment->body,
                        'source_id'   => $comment->id,
                        'source_type' => get_class($comment),
                        'to_user_id'  => 0,
                        'status'      => 1,
                        'supports'    => 0
                    ]);

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
                        'domain'=>$submission->data['domain']??'',
                        'img'=>$submission->data['img']??'',
                        'submission_username' => $submission_user->name,
                        'comment_content' => $comment->body
                    ])
                    ->log($user->name.'评论了动态', Feed::FEED_TYPE_COMMENT_READHUB_ARTICLE);
                return;
                break;
            case 'NewSubmission':
                return;
                event(new CreditEvent($this->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$this->message['submission_id'],''));
                //产生一条feed流
                $submission = Submission::find($this->message['submission_id']);
                feed()
                    ->causedBy($user)
                    ->performedOn($submission)
                    ->withProperties([
                        'view_url'=>$submission->data['url']??'',
                        'category_id'=>$submission->category_id,
                        'slug'=>$submission->slug,
                        'submission_title'=>$submission->title,
                        'domain'=>$submission->data['domain']??'',
                        'current_address_name' => $submission->data['current_address_name'],
                        'current_address_longitude' => $submission->data['current_address_longitude'],
                        'current_address_latitude'  => $submission->data['current_address_latitude'],
                        'img'=>$submission->data['img']??''])
                    ->log($user->name.'发布了动态', Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);
                return;
                break;
            case 'NewSubmissionUpVote':
                //文章点赞
                //产生一条feed流
                $submission = Submission::find($this->message['submission_id']);
                if ($submission->type != 'link') return;
                $submission_user = User::find($submission->user_id);
                $feed_event = 'NewSubmissionUpVote';
                $feed_target = $submission->id.'_'.$user->id;
                $is_feeded = RateLimiter::instance()->getValue($feed_event,$feed_target);
                if (!$is_feeded) {
                    feed()
                        ->causedBy($user)
                        ->performedOn($submission)
                        ->withProperties([
                            'view_url'=>$submission->data['url']??'',
                            'submission_username' => $submission_user->name,
                            'category_id'=>$submission->category_id,
                            'slug'=>$submission->slug,
                            'submission_title'=>$submission->title,
                            'domain'=>$submission->data['domain']??'',
                            'type'  => $submission->type,
                            'img'=>$submission->data['img']??''])
                        ->log($user->name.'赞了动态', Feed::FEED_TYPE_UPVOTE_READHUB_ARTICLE);
                    RateLimiter::instance()->increase($feed_event,$feed_target,3600);
                    $fields = [];
                    $fields[] = [
                        'title' => '文章标题',
                        'value' => $submission->title
                    ];
                    $fields[] = [
                        'title' => '文章地址',
                        'value' => config('app.readhub_url').'/c/'.$submission->category_id.'/'.$submission->slug
                    ];
                    try {
                        \Slack::to(config('slack.ask_activity_channel'))
                            ->attach(
                                [
                                    'fields' => $fields
                                ]
                            )
                            ->send('用户'.$user->id.'['.$user->name.']赞了文章');
                    } catch (\Exception $e) {
                        app('sentry')->captureException($e);
                    }
                }
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
