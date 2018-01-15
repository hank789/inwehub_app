<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\Credit;
use App\Models\Feed\Feed;
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
                //通知，自己除外
                if ($source->user_id != $support->user_id) {
                    $source->user->notify(new NewSupport($source->user_id, $support));
                }
                //产生一条feed
                $feed_event = 'question_supported';
                $feed_target = $source->id.'_'.$support->user_id;
                $is_feeded = RateLimiter::instance()->getValue($feed_event,$feed_target);
                if (!$is_feeded) {
                    $question = $source->question;
                    if ($question->question_type == 1) {
                        $feed_question_title = '专业回答';
                        $feed_type = Feed::FEED_TYPE_UPVOTE_PAY_QUESTION;
                        $feed_url = '/askCommunity/major/'.$source->question_id;
                        $feed_answer_content = '';
                    } else {
                        $feed_question_title = '互动回答';
                        $feed_type = Feed::FEED_TYPE_UPVOTE_FREE_QUESTION;
                        $feed_url = '/askCommunity/interaction/'.$source->id;
                        $feed_answer_content = $source->getContentText();
                    }
                    feed()
                        ->causedBy($support->user)
                        ->performedOn($source)
                        ->withProperties([
                            'answer_user_name' => $source->user->name,
                            'question_title'   => $question->title,
                            'answer_content'   => $feed_answer_content,
                            'feed_url'         => $feed_url
                        ])
                        ->log($support->user->name.'赞了'.$feed_question_title, $feed_type);
                    RateLimiter::instance()->increase($feed_event,$feed_target,3600);
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
                break;
            default:
                return;
        }
        if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('upvote:'.get_class($source),$source->id.'_'.$support->user_id,0)) {
            switch ($support->supportable_type) {
                case 'App\Models\Answer':
                    $question = $source->question;
                    event(new Credit($support->user_id,CreditModel::KEY_NEW_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_NEW_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_NEW_UPVOTE),$support->supportable_id,'点赞回答'));
                    if ($question->question_type == 1) {
                        event(new Credit($source->user_id,CreditModel::KEY_ANSWER_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_ANSWER_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_ANSWER_UPVOTE),$support->supportable_id,'专业回答被点赞'));
                    } else {
                        event(new Credit($source->user_id,CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE),$support->supportable_id,'互动回答被点赞'));
                    }
                    break;
                case 'App\Models\Submission':
                    event(new Credit($support->user_id,CreditModel::KEY_NEW_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_NEW_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_NEW_UPVOTE),$support->supportable_id,'点赞动态分享'));
                    event(new Credit($source->user_id,CreditModel::KEY_READHUB_SUBMISSION_UPVOTE,Setting()->get('coins_'.CreditModel::KEY_READHUB_SUBMISSION_UPVOTE),Setting()->get('credits_'.CreditModel::KEY_READHUB_SUBMISSION_UPVOTE),$support->supportable_id,'动态分享被点赞'));
                    break;
            }
        }

        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'  => 'good',
                    'fields' => $fields
                ]
            )->send('用户'.$support->user->id.'['.$support->user->name.']赞了'.$title);
    }



}