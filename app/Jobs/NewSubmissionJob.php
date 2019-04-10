<?php

namespace App\Jobs;

use App\Logic\QuillLogic;
use App\Logic\TagsLogic;
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

        $user = User::find($submission->user_id);

        if (!$this->notifyAutoChannel) {
            event(new CreditEvent($submission->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$submission->id,'动态分享'));
        }

        RateLimiter::instance()->lock_acquire('upload-image-submission-'.$submission->id);
        $submission->increment('views');

        $typeName = '分享';
        $targetName = '';
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
                if (isset($submission->data['category_ids'])) {
                    foreach ($submission->data['category_ids'] as $category_id) {
                        $tagC = TagCategoryRel::where('tag_id',$submission->category_id)->where('category_id',$category_id)->first();
                        $tagC->calcRate();
                    }
                }
                $tag = Tag::find($submission->category_id);
                $tag->reviews += 1;
                $tag->save();
                $targetName = '在产品['.$tag->name.']';
                TagsLogic::delProductCache();
                dispatch(new UpdateProductInfoCache($tag->id));
                if (isset($submission->data['real_author']) && $submission->data['real_author']) {
                    $real_author = User::find($submission->data['real_author']);
                    $this->additionalSlackMsg .= '运营人员：'.formatSlackUser($real_author).';';
                }
                $url = config('app.mobile_url').'#/dianping/comment/'.$submission->slug;
                break;
        }
        if ($submission->type != 'review') {
            $members = [];
            if ($submission->group_id) {
                $group = Group::find($submission->group_id);

                $group->increment('articles');
                GroupMember::where('group_id',$group->id)->update(['updated_at'=>Carbon::now()]);
                RateLimiter::instance()->sClear('group_read_users:'.$group->id);
                $members = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('user_id')->toArray();
                //提到了人，还未去重
                $notified_uids = $this->handleSubmissionMentions($submission,$members);
                $notified_uids[$submission->user_id] = $submission->user_id;
                //通知圈主
                if ($submission->user_id != $group->user_id) {
                    $notified_uids[$group->user_id] = $group->user_id;
                    $group->user->notify((new NewSubmission($group->user_id,$submission))->delay(Carbon::now()->addMinutes(3)));
                }
                //圈主发布的内容通知圈子成员
                if (false && $submission->user_id == $group->user_id && $members) {
                    foreach ($members as $muid) {
                        if (isset($notified_uids[$muid])) continue;
                        $notified_uids[$muid] = $muid;
                        $mUser = User::find($muid);
                        $mUser->notify((new NewSubmission($muid,$submission))->delay(Carbon::now()->addMinutes(3)));
                    }
                }
                $targetName = '在圈子['.$group->name.']';
            }

            if (!$this->notifyAutoChannel) {
                feed()
                    ->causedBy($user)
                    ->performedOn($submission)
                    ->setGroup($submission->group_id)
                    ->setPublic($submission->public)
                    ->withProperties([
                        'submission_title'=>$submission->title
                    ])
                    ->log(($submission->hide?'匿名':$user->name).'发布了'.$typeName, Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);
            }



            //关注的用户接收通知
            $attention_users = Attention::where('source_type','=',get_class($user))->where('source_id','=',$user->id)->pluck('user_id')->toArray();

            foreach ($attention_users as $attention_uid) {
                if (isset($notified_uids[$attention_uid])) continue;
                //私密圈子的分享只通知圈子内的人
                if ($submission->group_id && !$group->public && $members && !in_array($attention_uid,$members)) continue;
                $attention_user = User::find($attention_uid);
                if ($attention_user) $attention_user->notify((new FollowedUserNewSubmission($attention_uid,$submission))->delay(Carbon::now()->addMinutes(3)));
            }

            $url = config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug;
            $submission->setKeywordTags();
        } else {
            feed()
                ->causedBy($user)
                ->performedOn($submission)
                ->setGroup($submission->group_id)
                ->setPublic($submission->public)
                ->anonymous($submission->hide)
                ->tags($submission->category_id)
                ->withProperties([
                    'submission_title'=>$submission->title
                ])
                ->log(($submission->hide?'匿名':$user->name).'发布了'.$typeName, Feed::FEED_TYPE_SUBMIT_READHUB_REVIEW);
            $data = $submission->data;
            $data['keywords'] = implode(',',$submission->tags->pluck('name')->toArray());
            $submission->data = $data;
            $submission->save();
            if (!$submission->hide) {
                //关注的用户接收通知
                $attention_users = Attention::where('source_type','=',get_class($user))->where('source_id','=',$user->id)->pluck('user_id')->toArray();
                //提到了人，还未去重
                $notified_uids = $this->handleSubmissionMentions($submission,[]);
                $notified_uids[$submission->user_id] = $submission->user_id;
                foreach ($attention_users as $attention_uid) {
                    if (isset($notified_uids[$attention_uid])) continue;
                    $attention_user = User::find($attention_uid);
                    if ($attention_user) $attention_user->notify((new FollowedUserNewSubmission($attention_uid,$submission))->delay(Carbon::now()->addMinutes(3)));
                }
            }
        }
        $submission->calculationRate();
        RateLimiter::instance()->lock_release('upload-image-submission-'.$submission->id);

        $submission->getRelatedProducts();
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
