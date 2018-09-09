<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\SystemNotify;
use App\Logic\BidLogic;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use QL\Ext\PhantomJs;
use QL\QueryList;
use App\Models\Scraper\BidInfo as BidInfoModel;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午7:42
 * @email: hank.huiwang@gmail.com
 */


class BidInfo extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:bid:info';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取招标信息';
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
        $ql = QueryList::getInstance();
        $ql2 = new QueryList();
        $ql2->use(PhantomJs::class,config('services.phantomjs.path'));
        $cookies = Setting()->get('scraper_jianyu360_cookie','');
        $count = 0;
        $startTime = time();
        if (empty($cookies)) {
            event(new SystemNotify('抓取招标信息未设置cookie，请到后台设置',[]));
            return;
        }
        $cookiesPcArr = explode('||',$cookies);

        if (!Setting()->get('scraper_proxy_address','')) {
            event(new SystemNotify('未设置爬虫代理，请到后台设置',[]));
            return;
        }

        $cookiesApp = Setting()->get('scraper_jianyu360_app_cookie','');
        $cookiesAppArr = explode('||',$cookiesApp);

        //最多10页
        for ($page=1;$page<=10;$page++) {
            sleep(rand(10, 15));
            for ($i = 0; $i < 5; $i++) {
                $content = $this->getHtmlData($ql, $page, $cookiesPcArr);
                if ($content) break;
            }
            $data = json_decode($content, true);
            if ($data) {
                $result = BidLogic::scraperSaveList($data, $ql2, $cookiesPcArr, $cookiesAppArr, $count);
                if (!$result) {
                    $endTime = time();
                    event(new SystemNotify('抓取了' . $count . '条最新招标信息，用时' . ($endTime - $startTime) . '秒', []));
                    return;
                }
            } else {
                event(new SystemNotify('抓取招最新标信息失败，对应cookie已失效或代理IP已耗尽，请到后台设置', []));
                return;
            }
        }
        if ($count >= 1) {
            $endTime = time();
            event(new SystemNotify('抓取了'.$count.'条最新招标信息，用时'.($endTime-$startTime).'秒',[]));
        }
    }

    protected function getHtmlData($ql,$page,$cookiesPcArr) {
        $ips = getProxyIps(1);
        if (!$ips) {
            event(new SystemNotify('代理IP已耗尽，请到后台设置', []));
            exit();
        }
        try {
            $content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
                'pageNumber' => $page,
                'pageType' => ''
            ],[
                'proxy' => $ips[0],
                'timeout' => 30,
                'headers' => [
                    'Host'    => 'www.jianyu360.com',
                    'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie'    => $cookiesPcArr[0]
                ]
            ])->getHtml();
        } catch (\Exception $e) {
            app('sentry')->captureException($e,['page'=>$page,'proxy'=>$ips[0],'cookiesPc'=>$cookiesPcArr[0]]);
            $content = null;
        }
        return $content;
    }
}