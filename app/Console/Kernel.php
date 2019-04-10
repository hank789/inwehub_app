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
        Commands\Sitemap::class,
        Commands\SitemapProduct::class,

        //定时任务
        Commands\Crontab\CalcGroupHot::class,
        Commands\Crontab\AwakeUser::class,
        Commands\Crontab\DealOvertimeTasks::class,
        Commands\Crontab\DailyUserActiveReport::class,
        Commands\Crontab\DailyRecommendReport::class,
        Commands\Crontab\DailyRegisterReport::class,
        Commands\Crontab\DailyReadReport::class,
        Commands\Crontab\RefreshCookieTask::class,
        Commands\Crontab\DailySubmitUrls::class,
        Commands\Crontab\RefreshWwwCache::class,
        Commands\Crontab\DailySubscribePush::class,
        Commands\Crontab\DailySubscribeEmail::class,
        Commands\Crontab\DailySubscribeWechatPush::class,
        //抓取脚本
        Commands\Scraper\WechatAuthor::class,
        Commands\Scraper\WechatPosts::class,
        Commands\Scraper\WechatMpPosts::class,
        Commands\Scraper\WechatMpAuthor::class,
        Commands\Scraper\RssPosts::class,
        Commands\Scraper\AtomPosts::class,
        Commands\Scraper\BidInfo::class,
        Commands\Scraper\BidSearch::class,
        Commands\Scraper\GoogleNews::class,
        Commands\Scraper\ItJuZi::class,
        Commands\Scraper\SapNews::class,
        Commands\Scraper\Indeed::class,
        Commands\Scraper\DoubanUser::class,
        Commands\Scraper\WallstreetcnNews::class,
        Commands\Scraper\Newrank::class,

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
        Commands\FixData\FixAutoTags::class,
        Commands\Init\ServiceCategories::class,
        Commands\Init\ServiceAllCategories::class,
        Commands\Init\ReviewAllProducts::class,
        Commands\Init\ReviewProducts::class,
        Commands\Init\ReviewSubmissions::class,
        Commands\Init\CalcProductReviews::class,
        Commands\Init\TranslateProducts::class,
        Commands\Init\TagRoles::class,
        Commands\Init\ItJuZiCompany::class,
        Commands\Init\TagProductDict::class,
        Commands\Init\ProductAlbum::class

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
        $schedule->command('backup:run')->daily()->at('02:00');
        $schedule->command('pay:settlement')->daily()->at('00:10')->withoutOverlapping();
        $schedule->command('user:check:rg_code')->daily()->at('00:30');
        $schedule->command('ac:check:coupon')->daily()->at('00:20');
        $schedule->command('crontab:calc-group-hot')->hourly();
        $schedule->command('crontab:daily:subscribe:push')->daily()->at('17:30');
        $schedule->command('crontab:daily:subscribe:email')->daily()->at('18:00');
        if (config('app.env') == 'production') {
            //10 8,12,14,16,18,20,22
            //$schedule->command('scraper:wechat:gzh:posts')->cron('10 10 * * *')->withoutOverlapping()->appendOutputTo('/tmp/gzh.txt');
            $schedule->command('scraper:newrank:wechat')->cron('30 9,19 * * *')->withoutOverlapping()->appendOutputTo('/tmp/newrank.txt');

            //$schedule->command('crontab:refresh:cookie:task')->hourly();
            $schedule->command('scraper:atom')->cron('0 8,10,16,20 * * *');
            $schedule->command('scraper:wallstreetcn:news')->cron('30 8,10,16,20 * * *');
            $schedule->command('scraper:rss')->cron('30 7,9,11,13,15,17,19,21,22,23 * * *');
            //$schedule->command('scraper:bid:info')->cron('20 12,19,21 * * *');
            $schedule->command('scraper:bid:search')->cron('40 7,10,13,17,21 * * *');
            $schedule->command('scraper:google:news')->hourly();
            $schedule->command('scraper:itjuzi:news')->cron('40 7,13,17,21 * * *');
            $schedule->command('scraper:sap:news')->cron('50 8,12,14,16,19,22 * * *');
            $schedule->command('scraper:indeed:jobs')->cron('55 7,10,13,15,17,21 * * *');
            $schedule->command('crontab:report:daily:user-active')->hourlyAt(59);
            $schedule->command('crontab:report:daily:register')->dailyAt('09:00');
            $schedule->command('sitemap:generate')->dailyAt('23:00');
            $schedule->command('sitemap:generate:product')->dailyAt('23:00');
            $schedule->command('crontab:report:daily:recommend')->cron('59 8,11,14,17,21,23 * * *');
            $schedule->command('crontab:report:daily:read')->hourlyAt(59);
            $schedule->command('crontab:refresh:www:cache')->twiceDaily(12,19);
            //$schedule->command('crontab:submit:daily:urls')->dailyAt('21:00');
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
