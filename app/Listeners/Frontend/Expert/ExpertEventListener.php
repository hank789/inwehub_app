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

            if($field == 'head_img_urls' && is_array($value)){
                foreach($value as $key=>$img_url){
                    if($img_url){
                        $item['title'] = $field.'_'.$key;
                        $item['value'] = $img_url;
                        $item['short'] = false;
                    }
                }
            }else{
                $item['title'] = $field;
                $item['value'] = $value;

                if(in_array($field,['description','head_img_urls'])){
                    $item['short'] = false;
                }else{
                    $item['short'] = true;
                }
            }

            $fields[] = $item;
        }
        $attach = [];
        $attach['fields'] = $fields;
        $attach['color'] = 'good';
        return \Slack::to(config('slack.ask_activity_channel'))
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