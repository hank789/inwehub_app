<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Attention;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\FollowedUserNewSubmission;
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

        event(new CreditEvent($submission->user_id,Credit::KEY_READHUB_NEW_SUBMISSION,Setting()->get('coins_'.Credit::KEY_READHUB_NEW_SUBMISSION),Setting()->get('credits_'.Credit::KEY_READHUB_NEW_SUBMISSION),$submission->id,''));
        //产生一条feed流
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
            ->log($user->name.'发布了'.($submission->type == 'link' ? '文章':'动态'), Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE);

        //关注的用户接收通知
        $attention_users = Attention::where('source_type','=',get_class($user))->where('source_id','=',$user->id)->pluck('user_id')->toArray();
        //提到了人，还未去重
        $notified_uids = $this->handleSubmissionMentions($submission);
        $notified_uids[$submission->user_id] = $submission->user_id;
        foreach ($attention_users as $attention_uid) {
            if (isset($notified_uids[$attention_uid])) continue;
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
            )->send('新文章提交');
    }



}