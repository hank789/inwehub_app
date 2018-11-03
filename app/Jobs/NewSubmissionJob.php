<?php

namespace App\Jobs;

use App\Logic\QuillLogic;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\User;
use App\Services\RateLimiter;
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

    public $notifyAutoChannel = false;

    public $additionalSlackMsg = '';


    public function __construct($id, $notifyAutoChannel = false, $additionalSlackMsg='')
    {
        $this->id = $id;
        $this->notifyAutoChannel = $notifyAutoChannel;
        $this->additionalSlackMsg = $additionalSlackMsg;
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
        $slackFields = [];
        foreach ($submission->data as $field=>$value){
            if ($value){
                if (!is_array($value) && in_array($field,['url','title'])) {
                    $slackFields[] = [
                        'title' => $field,
                        'value' => $field=='description'?QuillLogic::parseText($value):$value
                    ];
                }
            }
        }
        $submission->increment('views');

        $user = User::find($submission->user_id);

        event(new CreditEvent($submission->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$submission->id,'动态分享'));

        $typeName = '分享';
        switch ($submission->type) {
            case 'link':
            case 'text':
                $typeName = '分享';
                break;
            case 'article':
                $typeName = '文章';
                break;
            case 'review':
                $typeName = '点评';
                foreach ($submission->data['category_ids'] as $category_id) {
                    $tagC = TagCategoryRel::where('tag_id',$submission->category_id)->where('category_id',$category_id)->first();
                    $tagC->reviews += 1;
                    $tagC->reviews_rate_sum += $submission->rate_star;
                    $tagC->review_average_rate = bcdiv($tagC->reviews_rate_sum,$tagC->reviews,1);
                    $tagC->save();
                }
                $tag = Tag::find($submission->category_id);
                $tag->increment('reviews');
                $targetName = '在产品['.$tag->name.']';
                break;
        }
        if ($submission->type != 'review') {
            $group = Group::find($submission->group_id);

            $group->increment('articles');
            GroupMember::where('group_id',$group->id)->update(['updated_at'=>Carbon::now()]);
            RateLimiter::instance()->sClear('group_read_users:'.$group->id);
            feed()
                ->causedBy($user)
                ->performedOn($submission)
                ->setGroup($submission->group_id)
                ->setPublic($submission->public)
                ->tags($submission->tags()->pluck('tag_id')->toArray())
                ->withProperties([
                    'submission_title'=>$submission->title
                ])
                ->log($user->name.'发布了'.$typeName, Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);

            $members = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('user_id')->toArray();


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
                //私密圈子的分享只通知圈子内的人
                if (!$group->public && $members && !in_array($attention_uid,$members)) continue;
                $attention_user = User::find($attention_uid);
                if ($attention_user) $attention_user->notify((new FollowedUserNewSubmission($attention_uid,$submission))->delay(Carbon::now()->addMinutes(3)));
            }
            $targetName = '在圈子['.$group->name.']';
        }

        $submission->setKeywordTags();
        $submission->calculationRate();
        $url = config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug;
        $channel = config('slack.ask_activity_channel');
        if ($this->notifyAutoChannel) {
            $channel = config('slack.auto_channel');
        }
        return \Slack::to($channel)
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
            )->send($this->additionalSlackMsg.'用户'.formatSlackUser($user).$targetName.'提交了新'.$typeName);
    }
}
