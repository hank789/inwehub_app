<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Third\AliCdn\Cdn;
use Illuminate\Console\Command;

class RefreshWwwCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:refresh:www:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新官网的缓存';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cdn = new Cdn();
        $result = $cdn->refreshCache('https://www.inwehub.com/');
        $this->info($result);
    }

}