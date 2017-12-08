<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Article;
use App\Models\Collection;
use App\Models\Submission;
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
        switch ($collect->source_type) {
            case 'App\Models\Article':
                $object = Article::find($collect->source_id);
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
                $object = Submission::find($collect->source_id);
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
            default:
                return;
                break;
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