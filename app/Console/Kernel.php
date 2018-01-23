<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Test::class,
        Commands\Pay\Settlement::class,
        Commands\Pay\WechatQueryPay::class,
        Commands\Wechat\AddMenu::class,
        Commands\User\GenUuid::class,
        Commands\User\CheckRgCode::class,
        Commands\Activity\CheckCoupon::class,
        Commands\User\RefreshUserLoginToken::class,

        //阅读站
        Commands\Readhub\InitUser::class,

        //修复数据用
        Commands\FixData\ReadhubNotification::class,
        Commands\User\NewbieTask::class,
        Commands\FixData\FollowerNotification::class,
        Commands\FixData\FixCollect::class,
        Commands\FixData\MoneyLogNotification::class,
        Commands\User\GenRcCode::class,
        Commands\Readhub\SyncComment::class,
        Commands\User\CdnUserAvatar::class,
        Commands\Readhub\MigrateData::class,
        Commands\FixData\FixSubmissionSuport::class,
        Commands\FixData\FixSubmissionIds::class,
        Commands\FixData\CompanyData::class,
        Commands\FixData\AnswerFeedbackTask::class,
        Commands\FixData\FixTaskPriority::class,
        Commands\FixData\FixImData::class,
        Commands\FixData\NewUserFollowingNotification::class,
        Commands\FixData\AddPayForViewDoing::class,
        Commands\FixData\FixCredits::class,
        Commands\User\AddDefaultUserTag::class,
        Commands\FixData\FixTags::class,
        Commands\FixData\FixUserLevel::class,
        Commands\FixData\FixQuestionRate::class,


    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->twiceDaily();
        $schedule->command('pay:settlement')->daily()->at('00:10')->withoutOverlapping();
        $schedule->command('user:check:rg_code')->daily()->at('00:30');
        $schedule->command('ac:check:coupon')->daily()->at('00:20');

        //$schedule->command('scraper:wechat:author')->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
