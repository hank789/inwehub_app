<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Attention;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\FollowedUserNewSubmission;
use App\Notifications\NewSubmission;
use App\Traits\UsernameMentions;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Frontend\System\Credit as CreditEvent;

class SubmissionObserver implements ShouldQueue {

    use UsernameMentions;
    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听问题创建的事件。
     *
     * @param  Submission  $submission
     * @return void
     */
    public function created(Submission $submission)
    {

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
                        'value' => $value
                    ];
                }
            }
        }
        $user = User::find($submission->user_id);

        event(new CreditEvent($submission->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$submission->id,'动态分享'));
        $group = Group::find($submission->group_id);
        $members = [];
        if ($group->public) {
            //公开圈子的内容产生一条feed流
            feed()
                ->causedBy($user)
                ->performedOn($submission)
                ->tags($submission->tags()->pluck('tag_id')->toArray())
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
                ->log($user->name.'发布了'.($submission->type == 'link' ? '文章':'分享'), Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);
        } else {
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
            $group->user->notify(new NewSubmission($group->user_id,$submission));
        }
        //圈主发布的内容通知圈子成员
        if ($submission->user_id == $group->user_id && $members) {
            foreach ($members as $muid) {
                if (isset($notified_uids[$muid])) continue;
                $notified_uids[$muid] = $muid;
                $mUser = User::find($muid);
                $mUser->notify(new NewSubmission($muid,$submission));
            }
        }
        foreach ($attention_users as $attention_uid) {
            if (isset($notified_uids[$attention_uid])) continue;
            if ($members && !in_array($attention_uid,$members)) continue;
            $attention_user = User::find($attention_uid);
            $attention_user->notify(new FollowedUserNewSubmission($attention_uid,$submission));
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