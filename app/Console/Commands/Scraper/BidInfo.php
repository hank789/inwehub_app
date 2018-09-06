<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\SystemNotify;
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
        $cookie = Setting()->get('scraper_jianyu360_cookie','');
        $count = 0;
        $newBidIds = [];
        $startTime = time();
        if (empty($cookie)) {
            event(new SystemNotify('抓取招标信息未设置cookie，请到后台设置',[]));
            return;
        }
        //最多10页
        for ($page=1;$page<=10;$page++) {
            $content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
                'pageNumber' => $page,
                'pageType' => ''
            ],[
                'headers' => [
                    'Host'    => 'www.jianyu360.com',
                    'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie'    => $cookie
                ]
            ])->getHtml();
            $data = json_decode($content,true);
            if ($data) {
                foreach ($data['list'] as $item) {
                    $this->info($item['title']);
                    $bid = BidInfoModel::where('guid',$item['_id'])->first();
                    if ($bid) {
                        if (in_array($item['_id'],$newBidIds)) {
                            continue;
                        } else {
                            if ($count >= 1) {
                                $endTime = time();
                                event(new SystemNotify('抓取了'.$count.'条招标信息，用时'.($endTime-$startTime).'秒',[]));
                            }
                            return;
                        }
                    }
                    $newBidIds[] = $item['_id'];
                    $info = [
                        'guid' => $item['_id'],
                        'source_url' => $item['_id'],
                        'title' => $item['title'],
                        'projectname' => $item['projectname']??'',
                        'projectcode' => $item['projectcode']??'',
                        'buyer' => $item['buyer']??'',
                        'toptype' => $item['toptype']??'',
                        'subtype' => $item['subtype']??'',
                        'area' => $item['area']??'',
                        'budget' => $item['budget']??'',
                        'bidamount' => $item['bidamount']??'',
                        'bidopentime' => isset($item['bidopentime'])?date('Y-m-d H:i:s',$item['bidopentime']):'',
                        'industry' => $item['industry']??'',
                        's_subscopeclass' => $item['s_subscopeclass']??'',
                        'winner' => $item['winner']??'',
                        'publishtime' => isset($item['publishtime'])?date('Y-m-d H:i:s',$item['publishtime']):'',
                        'status' => 2
                    ];
                    sleep(rand(5,20));
                    $content = $ql2->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($item, $cookie){
                        $r->setUrl('https://www.jianyu360.com/article/content/'.$item['_id'].'.html');
                        //$r->setTimeout(10000); // 10 seconds
                        //$r->setDelay(5); // 3 seconds
                        $r->setHeaders([
                            'Host'   => 'www.jianyu360.com',
                            'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                            'Cookie' => $cookie
                        ]);
                        return $r;
                    });
                    $info['source_url'] = $content->find('a.com-original')->href;
                    $item['bid_html_body'] = $content->find('div.com-detail')->htmls()->first();
                    if (empty($info['source_url']) || empty($item['bid_html_body'])) {
                        event(new SystemNotify('抓取招标详情失败，对应cookie已失效，请到后台设置',[]));
                        return;
                    }
                    $info['detail'] = $item;
                    BidInfoModel::create($info);
                    $count ++;
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