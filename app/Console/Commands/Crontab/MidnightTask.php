<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Scraper\WechatMpInfo;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use Illuminate\Console\Command;

class MidnightTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:midnight:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '凌晨到6点的常规用于验证的任务';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $limit = RateLimiter::instance()->getValue('scraper_mp_freq',date('Y-m-d'));
        if ($limit) return;
        $spider = new MpSpider();
        $mpInfo = WechatMpInfo::where('status',1)->orderBy('update_time','asc')->first();
        $wz_list = $spider->getGzhInfo($mpInfo->wx_hao);
    }

}