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
        $count = 0;
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
        foreach ($keywords as $keyword) {
            sleep(rand(10,60));
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
                'proxy' => $ip['ip'].':'.$ip['port'],
                'headers' => [
                    'Host'    => 'www.jianyu360.com',
                    'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie'    => $cookie[rand(0,count($cookie)-1)]
                ]
            ])->getHtml();
            $data = json_decode($content,true);
            if ($data) {
                $result = BidLogic::scraperSaveList($data,$ql2,$cookie,$proxy['msg'],$count);
                if (!$result) {
                    if ($count >= 1) {
                        $endTime = time();
                        event(new SystemNotify('抓取了'.$count.'条招标信息，用时'.($endTime-$startTime).'秒',[]));
                    }
                    continue;
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
}