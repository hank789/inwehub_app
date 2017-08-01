<?php

namespace App\Listeners\Frontend\Auth;
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
        \Slack::send('User Logged In: '.$event->user->name);
    }

    /**
     * @param $event
     */
    public function onLoggedOut($event)
    {
        \Slack::send('User Logged Out: '.$event->user->name);

    }

    /**
     * @param $event
     */
    public function onRegistered($event)
    {
        // read站点同步注册用户
        $exist = ReadHubUser::where('username','=',$event->user->name)->first();
        $username = $event->user->name;
        if ($exist) {
            $username = $event->user->name.'_'.$event->user->id;
        }
        ReadHubUser::create([
            'username' => $username,
            'uuid'     => $event->user->uuid,
            'name'     => $event->user->name,
            'active'   => 1,
            'confirmed' => 1,
            'verfied'   => 1,
            'password' => $event->user->password,
            'avatar'   => $event->user->getAvatarUrl(),
            'info' => [
                'website' => null,
                'twitter' => null,
            ],
            'settings'  => [
                'font'                          => 'Lato',
                'sidebar_color'                 => 'Gray',
                'nsfw'                          => false,
                'nsfw_media'                    => false,
                'notify_submissions_replied'    => true,
                'notify_comments_replied'       => true,
                'notify_mentions'               => true,
                'exclude_upvoted_submissions'   => false,
                'exclude_downvoted_submissions' => true,
                'submission_small_thumbnail'    => true,
            ],
        ]);
        \Slack::send('User Registered: '.$event->user->name);
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
