<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
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


class BidSearch extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:bid:search {word?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据关键词搜索招标信息';
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
        $word = $this->argument('word');
        if (empty($word)) {
            $scraper_bid_keywords = Setting()->get('scraper_bid_keywords','SAP|信息化|供应链金融|供应链管理|供应链|平台|oracle|管理咨询|麦肯锡');
            $keywords = explode('|',$scraper_bid_keywords);
        } else {
            $keywords = [$word];
        }

        $ql = QueryList::getInstance();
        $ql2 = new QueryList();
        $ql2->use(PhantomJs::class,config('services.phantomjs.path'));
        $cookies = Setting()->get('scraper_jianyu360_cookie','');
        $startTime = time();
        if (empty($cookies)) {
            event(new ExceptionNotify('抓取招标信息未设置cookie，请到后台设置',[]));
            return;
        }
        $cookiesPcArr = explode('||',$cookies);

        if (!Setting()->get('scraper_proxy_address','')) {
            event(new ExceptionNotify('未设置爬虫代理，请到后台设置',[]));
            return;
        }

        $cookiesApp = Setting()->get('scraper_jianyu360_app_cookie','');
        $cookiesAppArr = explode('||',$cookiesApp);
        $allCount = 0;
        $count = 0;
        //validateProxyIps();
        foreach ($keywords as $key=>$keywordConfig) {
            if ($count <= 6 && $key>=1) {
                sleep((6-$count) * 10);
            }
            $keywordArr = explode('_',$keywordConfig);
            $keyword = $keywordArr[0];

            $count = 0;
            $data = null;
            for ($i=0;$i<5;$i++) {
                $content = $this->getHtmlData($ql,$keyword,$cookiesPcArr);
                if ($content) {
                    $data = json_decode($content,true);
                    if ($data['status']==1) {
                        break;
                    }
                }
                sleep(rand(20,40));
            }

            if ($data) {
                event(new SystemNotify('准备处理'.count($data['list']).'条['.$keyword.']招标信息'));
                $result = BidLogic::scraperSaveList($data,$ql2,$cookiesPcArr,$cookiesAppArr,$count,$keywordArr,true);
                $allCount += $count;
                if (!$result) {
                    $endTime = time();
                    event(new SystemNotify('抓取了'.$count.'条['.$keyword.']招标信息,忽略'.(count($data['list'])-$count).'条，已用时'.($endTime-$startTime).'秒'));
                    continue;
                }
            } else {
                event(new ExceptionNotify('抓取['.$keyword.']招标信息失败，对应cookie已失效或代理IP已耗尽，请到后台设置'));
                return;
            }

        }
        $totalCount = \App\Models\Scraper\BidInfo::where('status',1)->count();
        if ($allCount >= 1 || $totalCount >= 1) {
            $endTime = time();
            event(new SystemNotify('新抓取'.$allCount.'条(共'.$totalCount.'条)招标信息，请及时去后台处理.用时'.($endTime-$startTime).'秒',[]));
        }
    }

    protected function getHtmlData($ql,$keyword,$cookiesPcArr) {
        /*$ips = getProxyIps(1);
        if (!$ips) {
            event(new ExceptionNotify('代理IP已耗尽，请到后台设置', []));
            exit();
        }
        $ip = $ips[0];*/
        $cookie = $cookiesPcArr[rand(0,count($cookiesPcArr)-1)];
        try {
            //全文搜索返回全部500条信息
            $content = $ql->post('https://www.jianyu360.com/front/pcAjaxReq',[
                'pageNumber' => 1,
                'reqType' => 'bidSearch',
                'searchvalue' => $keyword,
                'area' => '',
                'subtype' => '',
                'publishtime' => '',
                'selectType' => 'all',
                'minprice' => '',
                'maxprice' => '',
                'industry' => '',
                'tabularflag' => 'Y'
            ],[
                //'proxy' => $ip,
                'timeout' => 6,
                'headers' => [
                    'Host'    => 'www.jianyu360.com',
                    'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie'    => $cookie
                ]
            ])->getHtml();
        } catch (\Exception $e) {
            //deleteProxyIp($ip);
            app('sentry')->captureException($e,['keyword'=>$keyword,'cookiesPc'=>$cookie]);
            $content = null;
        }
        return $content;
    }
}