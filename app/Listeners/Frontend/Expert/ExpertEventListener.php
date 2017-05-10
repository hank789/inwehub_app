<?php namespace App\Listeners\Frontend\Expert;
/**
 * @author: wanghui
 * @date: 2017/5/10 下午9:56
 * @email: wanghui@yonglibao.com
 */
use App\Events\Frontend\Expert\Recommend;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;

class ExpertEventListener implements ShouldQueue {
    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param Recommend $event
     */
    public function recommend($event)
    {
        $fields = [];
        foreach($event as $field=>$value){
            $item = [];
            $item['title'] = $field;
            $item['value'] = $value;
            if(in_array($field,['description','head_img_url'])){
                $item['short'] = false;
            }else{
                $item['short'] = true;
            }
            $fields[] = $item;
        }
        $attach = [];
        $attach['fields'] = $fields;
        $attach['color'] = 'good';
        return \Slack::to('#app_activity')
            ->attach($attach)
            ->send('用户['.$event->user_id.']推荐了专家');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Recommend::class,
            'App\Listeners\Frontend\Expert\ExpertEventListener@recommend'
        );
    }

}