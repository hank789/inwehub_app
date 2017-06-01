<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\QQ\QqExtendSocialite@handle',
            'SocialiteProviders\Weibo\WeiboExtendSocialite@handle',
            'SocialiteProviders\Weixin\WeixinExtendSocialite@handle',
            'SocialiteProviders\WeixinWeb\WeixinWebExtendSocialite@handle',
        ]
    ];

    /**
     * Class event subscribers.
     *
     * @var array
     */
    protected $subscribe = [
        /*
         * Frontend Subscribers
         */
        \App\Listeners\Frontend\Expert\ExpertEventListener::class,
        \App\Listeners\Frontend\WithdrawEventListener::class,
        \App\Listeners\Frontend\Question\QuestionEventListener::class,
        \App\Listeners\Frontend\Answer\AnswerEventListener::class,


        /*
         * Auth Subscribers
         */
        \App\Listeners\Frontend\Auth\UserEventListener::class,
        \App\Listeners\Frontend\SystemEventListener::class,

        /*
         * Backend Subscribers
         */

        /*
         * Access Subscribers
         */
        \App\Listeners\Backend\Access\User\UserEventListener::class,
        \App\Listeners\Backend\Access\Role\RoleEventListener::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
