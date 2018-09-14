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
        Commands\InitEs::class,

        //定时任务
        Commands\Crontab\CalcGroupHot::class,
        Commands\Crontab\AwakeUser::class,
        Commands\Crontab\DealOvertimeTasks::class,
        //抓取脚本
        Commands\Scraper\WechatAuthor::class,
        Commands\Scraper\WechatPosts::class,
        Commands\Scraper\RssPosts::class,
        Commands\Scraper\AtomPosts::class,
        Commands\Scraper\BidInfo::class,
        Commands\Scraper\BidSearch::class,
        Commands\Scraper\GoogleNewsAccenture::class,

        //活动脚本
        Commands\Activity\SendSms124425049::class,

        //修复数据用
        Commands\User\NewbieTask::class,
        Commands\FixData\FixCollect::class,
        Commands\User\GenRcCode::class,
        Commands\User\CdnUserAvatar::class,
        Commands\FixData\FixSubmissionSuport::class,
        Commands\FixData\CompanyData::class,
        Commands\FixData\AnswerFeedbackTask::class,
        Commands\FixData\FixTaskPriority::class,
        Commands\FixData\AddPayForViewDoing::class,
        Commands\FixData\FixCredits::class,
        Commands\User\AddDefaultUserTag::class,
        Commands\FixData\FixUserLevel::class,
        Commands\FixData\FixQuestionRate::class,
        Commands\User\GenGeohash::class,
        Commands\FixData\FixSupportAddRefer::class,
        Commands\FixData\FixFeedTags::class,
        Commands\User\GenUserInfoCompletePercent::class,
        Commands\FixData\AddUserToGroup::class,
        Commands\FixData\FixFeedGroup::class,
        Commands\FixData\AddTags::class,
        Commands\FixData\FixRecommendDate::class,
        Commands\FixData\FixRecommendTags::class,
        Commands\FixData\FixAutoTags::class

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
        $schedule->command('crontab:calc-group-hot')->hourly();
        if (config('app.env') == 'production') {
            //$schedule->command('scraper:wechat:posts')->cron('30 7,9,11,13,15,17,19,21,23 * * *')->withoutOverlapping();
            $schedule->command('scraper:atom')->cron('0 8,16,20 * * *');
            $schedule->command('scraper:rss')->cron('30 7,13,19,21 * * *');
            $schedule->command('scraper:bid:info')->cron('20 12,19,21 * * *');
            $schedule->command('scraper:bid:search')->cron('40 7,10,13,17,21 * * *');
        }
        $schedule->command('crontab:awake-user')->twiceDaily(9,19);
        $schedule->command('crontab:deal-overtime-task')->daily()->at('05:00')->withoutOverlapping();
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
