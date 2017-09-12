<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Article;
use App\Models\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;

class CollectObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;

    /**
     * 监听问题创建的事件。
     *
     * @param  Collection  $collect
     * @return void
     */
    public function created(Collection $collect)
    {
        switch ($collect->source_type) {
            case 'App\Models\Article':
                $object = Article::find($collect->source_id);
                break;
            default:
                return;
                break;
        }
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
        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'     => 'good',
                    'fields' => $fields
                ]
            )->send('用户['.$collect->user->name.']报名了活动');
    }



}