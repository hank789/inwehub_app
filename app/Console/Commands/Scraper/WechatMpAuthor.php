<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatMpList;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * @author: wanghui
 * @date: 2017/4/13 下午7:42
 * @email: hank.huiwang@gmail.com
 */


class WechatMpAuthor extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:gzh:author';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取微信公众号(根据公众号平台抓)';
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
        $spider = new MpSpider();
        $all = WechatMpList::get();
        foreach ($all as $item) {
            $info = WechatMpInfo::where('wx_hao',$item->wx_hao)->first();
            if ($info) {
                $item->delete();
            } else {
                $data = $spider->getGzhInfo($item->wx_hao);
                if ($data) {
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
                        $item->delete();
                    }
                } else {
                    Artisan::call('scraper:wechat:author');
                }
            }
        }
    }
}