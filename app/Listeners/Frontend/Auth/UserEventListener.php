<?php

namespace App\Listeners\Frontend\Auth;
use App\Logic\TaskLogic;
use App\Models\Readhub\ReadHubUser;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class UserEventListener.
 */
class UserEventListener implements ShouldQueue
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
    public function onLoggedIn($event)
    {
        \Slack::send('用户登录: '.$event->user->name);
    }

    /**
     * @param $event
     */
    public function onLoggedOut($event)
    {
        \Slack::send('用户登出: '.$event->user->name);

    }

    /**
     * @param $event
     */
    public function onRegistered($event)
    {
        // 生成新手任务
        // 完善用户信息
        TaskLogic::task($event->user->id,'newbie_complete_userinfo',0,'newbie_complete_userinfo');
        // 阅读评论
        TaskLogic::task($event->user->id,'newbie_readhub_comment',0,'newbie_readhub_comment');
        // 发起提问
        TaskLogic::task($event->user->id,'newbie_ask',0,'newbie_ask');
        $title = '';
        if ($event->user->company) {
            $title .= ';公司：'.$event->user->company;
        }
        if ($event->user->title) {
            $title .= ';职位：'.$event->user->title;
        }
        if ($event->user->email) {
            $title .= ';邮箱：'.$event->user->email;
        }
        \Slack::send('新用户注册: '.$event->user->name.$title);
    }

    /**
     * @param $event
     */
    public function onConfirmed($event)
    {
        \Slack::send('User Confirmed: '.$event->user->name);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\Frontend\Auth\UserLoggedIn::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onLoggedIn'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserLoggedOut::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onLoggedOut'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserRegistered::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onRegistered'
        );

        $events->listen(
            \App\Events\Frontend\Auth\UserConfirmed::class,
            'App\Listeners\Frontend\Auth\UserEventListener@onConfirmed'
        );
    }
}
