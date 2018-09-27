<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatMpList;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\WechatSpider;
use Illuminate\Console\Command;

/**
 * @author: wanghui
 * @date: 2017/4/13 下午7:42
 * @email: hank.huiwang@gmail.com
 */


class WechatAuthor extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:author';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取微信公众号';
    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$path = config('app.spider_path');
        if($path){
            shell_exec('cd '.$path.' && python auto_add_mp.py >> /tmp/auto_add_mp.log');
        }*/
        $all = WechatMpList::get();
        $domain = 'sogou';
        $members = RateLimiter::instance()->sMembers('proxy_ips_deleted_'.$domain);
        foreach ($members as $member) {
            deleteProxyIp($member,$domain);
        }
        validateProxyIps('sogou');
        getProxyIps(5,'sogou');
        $spider = new WechatSpider();
        foreach ($all as $item) {
            $info = WechatMpInfo::where('wx_hao',$item->wx_hao)->first();
            if ($info) {
                $item->delete();
            } else {
                $data = $spider->getGzhInfo($item->wx_hao);
                if ($data['name']) {
                    $info = WechatMpInfo::where('wx_hao',$item->wx_hao)->first();
                    if ($info) {
                        $item->delete();
                    } else {
                        WechatMpInfo::create([
                            'name' => $data['name'],
                            'wx_hao' => $data['wechatid'],
                            'company' => $data['company'],
                            'description' => $data['description'],
                            'logo_url' => $data['img'],
                            'qr_url' => $data['qrcode'],
                            'wz_url' => $data['url'],
                            'last_qunfa_id' => $data['last_qunfa_id'],
                            'create_time' => date('Y-m-d H:i:s')
                        ]);
                    }
                } else {
                    event(new ExceptionNotify('抓取微信公众号失败：'.$item->wx_hao));
                }
                $item->delete();
            }
        }
    }
}