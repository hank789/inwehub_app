<?php

namespace App\Listeners\Frontend;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        \Slack::to('#app_ask_activity')->send('用户['.$event->user->name.']对平台的意见反馈:'.$event->content);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\Frontend\System\Feedback::class,
            'App\Listeners\Frontend\SystemEventListener@feedback'
        );
    }
}
