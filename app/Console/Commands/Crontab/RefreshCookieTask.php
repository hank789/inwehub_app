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

class RefreshCookieTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:refresh:cookie:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新cookie的任务';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $spider = new MpSpider();
        $spider->refreshCookie();
        $curlShadowsocks = RateLimiter::instance()->getValue('curlShadowsocks','success');
        if ($curlShadowsocks === '0') {
            shell_exec('sslocal -c /etc/shadowsocks/shadowsocks.json -d stop');
            shell_exec('sslocal -c /etc/shadowsocks/shadowsocks.json -d start');
            RateLimiter::instance()->setVale('curlShadowsocks','success',1,60*60*24);
        }
    }

}