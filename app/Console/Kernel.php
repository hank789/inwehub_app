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
        Commands\Component\ComponentArchiveCommand::class,
        Commands\Component\ComponentCommand::class,
        Commands\Component\ComponentLinkCommand::class,
        Commands\Scraper\RssPosts::class,
        Commands\Scraper\AtomPosts::class,
        Commands\Scraper\WechatPosts::class,
        Commands\Scraper\WechatAuthor::class,
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
        $schedule->command('scraper:rss')->everyTenMinutes();
        $schedule->command('scraper:atom')->everyTenMinutes();
        $schedule->command('scraper:wechat:author')->everyThirtyMinutes();
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
