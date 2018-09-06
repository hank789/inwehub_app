<?php

namespace App\Jobs;

use App\Logic\QuillLogic;
use App\Models\Submission;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Frontend\System\Credit as CreditEvent;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Notifications\FollowedUserNewSubmission;
use App\Notifications\NewSubmission;
use App\Traits\UsernameMentions;


class NewSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UsernameMentions;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $id;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $submission = Submission::find($this->id);
        if (!$submission) return;
        if ($submission->status == 0) return;
        $submission->setKeywordTags();
        $submission->calculationRate();
        $slackFields = [];
        foreach ($submission->data as $field=>$value){
            if ($value){
                if (is_array($value)) {
                    foreach ($value as $key => $item) {
                        $slackFields[] = [
                            'title' => $field.$key,
                            'value' => $item
                        ];
                    }
                } else {
                    $slackFields[] = [
                        'title' => $field,
                        'value' => $field=='description'?QuillLogic::parseText($value):$value
                    ];
                }
            }
        }
        $user = User::find($submission->user_id);

        event(new CreditEvent($submission->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$submission->id,'动态分享'));
        $group = Group::find($submission->group_id);
        $members = [];
        feed()
            ->causedBy($user)
            ->performedOn($submission)
            ->setGroup($submission->group_id)
            ->setPublic($submission->public)
            ->tags($submission->tags()->pluck('tag_id')->toArray())
            ->withProperties([
                'submission_title'=>$submission->title
            ])
            ->log($user->name.'发布了'.($submission->type == 'article' ? '文章':'分享'), Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);
        if (!$group->public) {
            //私密圈子的分享只通知圈子内的人
            $members = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('user_id')->toArray();
        }

        //关注的用户接收通知
        $attention_users = Attention::where('source_type','=',get_class($user))->where('source_id','=',$user->id)->pluck('user_id')->toArray();
        //提到了人，还未去重
        $notified_uids = $this->handleSubmissionMentions($submission,$members);
        $notified_uids[$submission->user_id] = $submission->user_id;
        //通知圈主
        if ($submission->user_id != $group->user_id) {
            $notified_uids[$group->user_id] = $group->user_id;
            $group->user->notify((new NewSubmission($group->user_id,$submission))->delay(Carbon::now()->addMinutes(3)));
        }
        //圈主发布的内容通知圈子成员
        if ($submission->user_id == $group->user_id && $members) {
            foreach ($members as $muid) {
                if (isset($notified_uids[$muid])) continue;
                $notified_uids[$muid] = $muid;
                $mUser = User::find($muid);
                $mUser->notify((new NewSubmission($muid,$submission))->delay(Carbon::now()->addMinutes(3)));
            }
        }
        foreach ($attention_users as $attention_uid) {
            if (isset($notified_uids[$attention_uid])) continue;
            if ($members && !in_array($attention_uid,$members)) continue;
            $attention_user = User::find($attention_uid);
            if ($attention_user) $attention_user->notify((new FollowedUserNewSubmission($attention_uid,$submission))->delay(Carbon::now()->addMinutes(3)));
        }

        $url = config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug;
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'text' => strip_tags($submission->title),
                    'pretext' => '[链接]('.$url.')',
                    'author_name' => $user->name,
                    'author_link' => $url,
                    'mrkdwn_in' => ['pretext'],
                    'color'     => 'good',
                    'fields' => $slackFields
                ]
            )->send('用户'.formatSlackUser($user).'在圈子['.$group->name.']提交了新分享');
    }
}
