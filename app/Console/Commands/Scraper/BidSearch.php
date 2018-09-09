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
        $allCount = 0;
        foreach ($keywords as $keyword) {
            sleep(rand(60,70));
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
            }

            $fields = [];
            if ($data) {
                event(new SystemNotify('准备处理'.count($data['list']).'条['.$keyword.']招标信息'));
                $result = BidLogic::scraperSaveList($data,$ql2,$cookiesPcArr,$cookiesAppArr,$count);
                $allCount += $count;
                if (!$result) {
                    $endTime = time();
                    $fields[] = [
                        'title'=>'data',
                        'value'=>json_encode($data)
                    ];
                    event(new SystemNotify('抓取了'.$count.'条['.$keyword.']招标信息，用时'.($endTime-$startTime).'秒',$fields));
                    continue;
                }
            } else {
                event(new SystemNotify('抓取['.$keyword.']招标信息失败，对应cookie已失效或代理IP已耗尽，请到后台设置',$fields));
                return;
            }

        }

        if ($count >= 1) {
            $endTime = time();
            event(new SystemNotify('根据关键词抓取了'.$allCount.'条招标信息，用时'.($endTime-$startTime).'秒',[]));
        }
    }

    protected function getHtmlData($ql,$keyword,$cookiesPcArr) {
        $ips = getProxyIps(1);
        if (!$ips) {
            event(new SystemNotify('代理IP已耗尽，请到后台设置', []));
            exit();
        }
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
                'proxy' => $ips[0],
                'timeout' => 60,
                'headers' => [
                    'Host'    => 'www.jianyu360.com',
                    'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie'    => $cookie
                ]
            ])->getHtml();
        } catch (\Exception $e) {
            app('sentry')->captureException($e,['keyword'=>$keyword,'proxy'=>$ips[0],'cookiesPc'=>$cookie]);
            $content = null;
        }
        return $content;
    }
}