<?php

namespace App\Listeners\Frontend\Auth;
use App\Events\Frontend\Auth\UserRegistered;
use App\Logic\TaskLogic;
use App\Models\Readhub\ReadHubUser;
use App\Models\User;
use App\Models\UserOauth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Redis;

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
     * @param UserRegistered $event
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
        if ($event->oauthDataId) {
            $oauthData = UserOauth::find($event->oauthDataId);
            $event->user->avatar = saveImgToCdn($oauthData->avatar);
            $event->user->save();
        }
        $title = '';
        if ($event->user->company) {
            $title .= ';公司：'.$event->user->company;
            Redis::connection()->hset('user_company_level',$event->user->id,$event->user->company);
        }
        if ($event->user->title) {
            $title .= ';职位：'.$event->user->title;
        }
        if ($event->user->email) {
            $title .= ';邮箱：'.$event->user->email;
        }
        if ($event->user->rc_uid) {
            $rc_user = User::find($event->user->rc_uid);
            $title .= ';邀请者：'.$rc_user->name;
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
