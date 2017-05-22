<?php

namespace App\Listeners\Frontend;
use App\Events\Frontend\System\Feedback;
use App\Events\Frontend\System\Push;
use App\Models\UserDevice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Getui;

/**
 * Class UserEventListener.
 */
class SystemEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @param $event
     */
    public function feedback($event)
    {
        \Slack::to(config('slack.ask_activity_channel'))->send('用户['.$event->user->name.']['.$event->user->mobile.']对平台的意见反馈:'.$event->content);
    }

    /**
     * 推送事件
     * @param Push $event
     */
    public function push($event){
        $devices = UserDevice::where('user_id',$event->user->id)->where('status',1)->get();

        $data = [
            'title' => $event->title,
            'body'  => $event->body,
            'text'  => $event->body,
            'content' => json_encode($event->content),
            'payload' => $event->payload
        ];
        foreach($devices as $device){
            Getui::pushMessageToSingle($device->client_id,$data,$event->template_id);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Feedback::class,
            'App\Listeners\Frontend\SystemEventListener@feedback'
        );
        $events->listen(
            Push::class,
            'App\Listeners\Frontend\SystemEventListener@push'
        );
    }
}
