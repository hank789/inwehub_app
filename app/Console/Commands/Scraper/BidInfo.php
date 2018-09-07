<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\SystemNotify;
use App\Logic\BidLogic;
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
        $newBidIds = [];
        $startTime = time();
        if (empty($cookies)) {
            event(new SystemNotify('抓取招标信息未设置cookie，请到后台设置',[]));
            return;
        }
        $cookie = explode('||',$cookies);

        $proxy = json_decode(file_get_contents(Setting()->get('scraper_proxy_address','')),true);
        if (!$proxy) {
            event(new SystemNotify('未设置爬虫代理，请到后台设置',[]));
            return;
        }
        $ip = $proxy['msg'][rand(0,count($proxy['msg'])-1)];
        //最多10页
        for ($page=1;$page<=10;$page++) {
            sleep(rand(5,10));
            for ($i=0;$i<count($proxy['msg']);$i++) {
                $content = $this->getHtmlData($ql,$page,$proxy['msg'][$i],$cookie);
                if ($content) break;
            }
            $data = json_decode($content,true);
            if ($data) {
                $result = BidLogic::scraperSaveList($data,$ql2,$cookie,$proxy['msg'],$count);
                if (!$result) {
                    if ($count >= 1) {
                        $endTime = time();
                        event(new SystemNotify('抓取了'.$count.'条招标信息，用时'.($endTime-$startTime).'秒',[]));
                    }
                    return;
                }
            } else {
                event(new SystemNotify('抓取招标信息失败，对应cookie已失效，请到后台设置',[]));
                return;
            }
        }
        if ($count >= 1) {
            $endTime = time();
            event(new SystemNotify('抓取了'.$count.'条招标信息，用时'.($endTime-$startTime).'秒',[]));
        }
    }

    protected function getHtmlData($ql,$page,$ip,$cookie) {
        try {
            $content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
                'pageNumber' => $page,
                'pageType' => ''
            ],[
                'proxy' => $ip['ip'].':'.$ip['port'],
                'headers' => [
                    'Host'    => 'www.jianyu360.com',
                    'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie'    => $cookie[rand(0,count($cookie)-1)]
                ]
            ])->getHtml();
        } catch (\Exception $e) {
            $content = null;
        }
        return $content;
    }
}