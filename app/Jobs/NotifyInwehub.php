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
                break;
            case 'NewSubmission':
                return;
                break;
            case 'NewSubmissionUpVote':
                //文章点赞
                $submission = Submission::find($this->message['submission_id']);
                $submission_user = User::find($submission->user_id);
                $feed_event = 'NewSubmissionUpVote';
                $feed_target = $submission->id.'_'.$user->id;
                $is_feeded = RateLimiter::instance()->getValue($feed_event,$feed_target);
                if (!$is_feeded) {
                    //feed流聚合展示
                    /*feed()
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
                        ->log($user->name.'赞了文章', Feed::FEED_TYPE_UPVOTE_READHUB_ARTICLE);*/
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
