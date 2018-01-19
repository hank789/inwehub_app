<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\Credit;
use App\Models\Collection;
use App\Models\Credit as CreditModel;
use App\Services\RateLimiter;
use Illuminate\Contracts\Queue\ShouldQueue;

class CollectObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 监听问题创建的事件。
     *
     * @param  Collection  $collect
     * @return void
     */
    public function created(Collection $collect)
    {
        $object = $collect->source;
        switch ($collect->source_type) {
            case 'App\Models\Article':
                $fields[] = [
                    'title' => '活动标题',
                    'value' => $object->title,
                    'short' => false
                ];
                $fields[] = [
                    'title' => '活动地址',
                    'value' => route('blog.article.detail',['id'=>$object->id]),
                    'short' => false
                ];
                $title = '报名了活动';
                break;
            case 'App\Models\Submission':
                $fields[] = [
                    'title' => '标题',
                    'value' => $object->title,
                    'short' => false
                ];
                $fields[] = [
                    'title' => '地址',
                    'value' => config('app.mobile_url').'#/c/'.$object->category_id.'/'.$object->slug,
                    'short' => false
                ];
                $title = '收藏了文章';
                break;
            case 'App\Models\Answer':
                $title = '收藏了回复';
                $fields[] = [
                    'title' => '回复内容',
                    'value' => $object->getContentText(),
                    'short' => false
                ];
                break;
            default:
                return;
                break;
        }
        if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('collect:'.get_class($object),$collect->source_id.'_'.$collect->user_id,0)) {
            event(new Credit($collect->user_id,CreditModel::KEY_NEW_COLLECT,Setting()->get('coins_'.CreditModel::KEY_NEW_COLLECT),Setting()->get('credits_'.CreditModel::KEY_NEW_COLLECT),$collect->id,'收藏成功'));
            switch ($collect->source_type) {
                case 'App\Models\Article':
                    event(new Credit($object->user_id,CreditModel::KEY_PRO_OPPORTUNITY_SIGNED,Setting()->get('coins_'.CreditModel::KEY_PRO_OPPORTUNITY_SIGNED),Setting()->get('credits_'.CreditModel::KEY_PRO_OPPORTUNITY_SIGNED),$collect->id,'项目机遇被报名'));
                    break;
                case 'App\Models\Submission':
                    event(new Credit($object->user_id,CreditModel::KEY_READHUB_SUBMISSION_COLLECT,Setting()->get('coins_'.CreditModel::KEY_READHUB_SUBMISSION_COLLECT),Setting()->get('credits_'.CreditModel::KEY_READHUB_SUBMISSION_COLLECT),$collect->id,'动态分享被收藏'));
                    break;
                case 'App\Models\Answer':
                    event(new Credit($object->user_id,CreditModel::KEY_COMMUNITY_ANSWER_COLLECT,Setting()->get('coins_'.CreditModel::KEY_COMMUNITY_ANSWER_COLLECT),Setting()->get('credits_'.CreditModel::KEY_COMMUNITY_ANSWER_COLLECT),$collect->id,'回答被收藏'));
                    break;
            }
        }

        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'     => 'good',
                    'fields' => $fields
                ]
            )->send('用户'.$collect->user->id.'['.$collect->user->name.']'.$title);
    }



}