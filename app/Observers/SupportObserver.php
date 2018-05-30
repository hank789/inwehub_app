<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\Credit;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Support;
use App\Notifications\NewSupport;
use App\Services\RateLimiter;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Credit as CreditModel;

class SupportObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听点赞事件。
     *
     * @param  Support  $support
     * @return void
     */
    public function created(Support $support)
    {
        $source = $support->source;
        $fields = [];
        $title = '';
        $notified = [];
        if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('upvote:'.get_class($source),$source->id.'_'.$support->user_id,0)) {
            switch ($support->supportable_type) {
                case 'App\Models\Answer':
                    $title = '回答';
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

                    $question = $source->question;
                    if ($question->question_type == 1) {
                        $feed_question_title = '专业回答';
                        $feed_type = Feed::FEED_TYPE_UPVOTE_PAY_QUESTION;
                    } else {
                        $feed_question_title = '互动回答';
                        $feed_type = Feed::FEED_TYPE_UPVOTE_FREE_QUESTION;
                    }
                    feed()
                        ->causedBy($support->user)
                        ->performedOn($source)
                        ->tags($question->tags()->pluck('tag_id')->toArray())
                        ->log($support->user->name.'赞了'.$feed_question_title, $feed_type);


                    event(new Credit($support->user_id,CreditModel::KEY_NEW_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_NEW_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_NEW_UPVOTE),$support->id,'点赞回答'));
                    if ($question->question_type == 1) {
                        event(new Credit($source->user_id,CreditModel::KEY_ANSWER_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_ANSWER_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_ANSWER_UPVOTE),$support->id,'专业回答被点赞'));
                    } else {
                        event(new Credit($source->user_id,CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE),$support->id,'互动回答被点赞'));
                    }
                    break;
                case 'App\Models\Submission':
                    $title = '动态';
                    $fields[] = [
                        'title' => '标题',
                        'value' => $source->formatTitle()
                    ];
                    $fields[] = [
                        'title' => '地址',
                        'value' => config('app.mobile_url').'#/c/'.$source->category_id.'/'.$source->slug
                    ];
                    foreach ($source->data as $field=>$value){
                        if ($value){
                            if (is_array($value)) {
                                foreach ($value as $key => $item) {
                                    $fields[] = [
                                        'title' => $field.$key,
                                        'value' => $item
                                    ];
                                }
                            } else {
                                $fields[] = [
                                    'title' => $field,
                                    'value' => $value
                                ];
                            }
                        }
                    }
                    event(new Credit($support->user_id,CreditModel::KEY_NEW_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_NEW_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_NEW_UPVOTE),$support->id,'点赞动态分享'));
                    event(new Credit($source->user_id,CreditModel::KEY_READHUB_SUBMISSION_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_READHUB_SUBMISSION_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_READHUB_SUBMISSION_UPVOTE),$support->id,'动态分享被点赞'));
                    //通知专栏作者
                    if ($source->author_id && $source->author_id != $support->user_id) {
                        $group = Group::find($source->group_id);
                        $members = null;
                        if (!$group->public) {
                            //私密圈子的分享只通知圈子内的人
                            $members = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('user_id')->toArray();
                        }
                        if ($members === null || in_array($source->author_id,$members)) {
                            $notified[$source->author_id] = $source->author_id;
                            $source->author->notify(new NewSupport($source->user_id,$support));
                        }
                    }
                    break;
                default:
                    return;
                    break;
            }
            if ($source->user_id != $support->user_id && !isset($notified[$source->user_id])) {
                $source->user->notify(new NewSupport($source->user_id,$support));
            }
        }
        if ($fields) {
            \Slack::to(config('slack.ask_activity_channel'))
                ->disableMarkdown()
                ->attach(
                    [
                        'color'  => 'good',
                        'fields' => $fields
                    ]
                )->send('用户'.$support->user->id.'['.$support->user->name.']赞了'.$title);
        }
        return;
    }



}