<?php

namespace App\Console\Commands;

use App\Models\RecommendRead;
use App\Models\Scraper\BidInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Taggable;
use App\Services\BosonNLPService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PHPHtmlParser\Dom;
use QL\Ext\PhantomJs;
use QL\QueryList;


class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*$sUrl = 'https://m.lagou.com/search.json?city=%E5%85%A8%E5%9B%BD&positionName=sap&pageNo=1&pageSize=15';
        $aHeader = [
            'Accept: application/json',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'Cookie: _ga=GA1.2.845934384.1535426841; user_trace_token=20180828112721-465c1caa-aa72-11e8-b24b-5254005c3644; LGUID=20180828112721-465c2202-aa72-11e8-b24b-5254005c3644; index_location_city=%E5%85%A8%E5%9B%BD; JSESSIONID=ABAAABAAAGCABCCD28DF8209A7B49B1E86DFDDA7FC4CB8F; _ga=GA1.3.845934384.1535426841; fromsite="zhihu.hank.com:8080"; utm_source=""; _gid=GA1.2.1118280405.1535619468; Hm_lvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1535455700,1535455777,1535455805,1535626070; _gat=1; LGSID=20180831103210-0fb55e88-acc6-11e8-be55-525400f775ce; PRE_UTM=; PRE_HOST=; PRE_SITE=; PRE_LAND=https%3A%2F%2Fwww.lagou.com%2F; LGRID=20180831103238-207ec83e-acc6-11e8-b30a-5254005c3644; Hm_lpvt_4233e74dff0ae5bd0a3d81c6ccf756e6=1535682758',
            'Host: m.lagou.com',
            'Referer: https://m.lagou.com/search.html',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
            'X-Requested-With: XMLHttpRequest'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $sUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        //curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData));
        $sResult = curl_exec($ch);

        curl_close($ch);
        $s = json_decode($sResult,true);
        var_dump($s);*/
        $ql = QueryList::getInstance();
        // 安装时需要设置PhantomJS二进制文件路径
        $ql->use(PhantomJs::class,config('services.phantomjs.path'));
        //$h = file_get_contents(storage_path().'/app/attachments/test3.html');
        //$ql->html($h);

        //$bid_html_body = $ql->removeHead()->getHtml();
        //$dom = new Dom();
        //$dom->load($bid_html_body);
        //$html = $dom->find('pre#h_content');
        //var_dump((string)$html);
        //return;
        /*$content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
            'pageNumber' => 1,
            'pageType' => ''
        ],[
            'proxy' => '125.123.122.31:42480',
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;
        $content = $ql->post('https://www.jianyu360.com/jylab/supsearch/getNewBids',[
            'pageNumber' => 2,
            'pageType' => ''
        ],[
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;
        /*$content = $ql->post('https://www.jianyu360.com/front/pcAjaxReq',[
            'pageNumber' => 1,
            'reqType' => 'bidSearch',
            'searchvalue' => '系统',
            'area' => '',
            'subtype' => '',
            'publishtime' => '',
            'selectType' => 'title',
            'minprice' => '',
            'maxprice' => '',
            'industry' => '',
            'tabularflag' => 'Y'
        ],[
            'headers' => [
                'Host'    => 'www.jianyu360.com',
                'Referer' => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie'    => 'UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371'
            ]
        ])->getHtml();
        var_dump($content);
        return;*/
        //$ql = QueryList::get('https://www.lagou.com/jobs/list_前端?labelWords=&fromSearch=true&suginput=');
        $cookie = 'SESSIONID=aea5b8fc92dd29486aff8adbe209c4a5ba12f936; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536286450; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1536283507,1536286139';
        $content = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($cookie){
            //$r->setMethod('POST');
            $r->setUrl('https://www.jianyu360.com/jyapp/article/content/ABCY2EAfTIvJyksJFZhcHUJJzACHj1mZnB%2FKA4gPy43eFJzfzNUCZM%3D.html');
            /*$r->setRequestData([
                'keywords' => '',
                'publishtime' => '',
                'timeslot' => '',
                'area' => '',
                'subtype' => '',
                'minprice' => '',
                'maxprice' => '',
                'industry' => '',
                'selectType' => 'title'
            ]);*/
            //$r->setTimeout(10000); // 10 seconds
            //$r->setDelay(3); // 3 seconds
            //$r->addHeader('Cookie','UM_distinctid=1658ad731701d9-0a4842018c67e4-34677908-1fa400-1658ad731726e2; Hm_lvt_72331746d85dcac3dac65202d103e5d9=1535632683; SESSIONID=1cf035dc58c73fbf2e4d7cf8fa937eb6c2282cb8; Hm_lvt_d7bc90fd54f45f37f12967f13c4ba19a=1536135302; CNZZDATA1261815924=1954814009-1535630590-%7C1536137064; userid_secure=GycHKzoDekh6Vx0oKF8XQ1VWXWIjFx4FOh1EYQ==; Hm_lpvt_d7bc90fd54f45f37f12967f13c4ba19a=1536139371; Hm_lpvt_72331746d85dcac3dac65202d103e5d9=1536139371');
            $r->setHeaders([
                'Host'   => 'www.jianyu360.com',
                'Referer'       => 'https://www.jianyu360.com/jyapp/wxpush/bidinfo/1536284358',
                'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Cookie' => $cookie
            ]);
            return $r;
        });
        $source_url = $content->find('a.original')->href;
        $bid_html_body = $content->removeHead()->getHtml();
        $dom = new Dom();
        $dom->load($bid_html_body);
        $html = $dom->find('pre#h_content');
        var_dump($html->__toString());
        //$content = $ql->browser('http://36kr.com/p/5151347.html?ktm_source=feed')->find('link[href*=.ico]')->href;
        var_dump($source_url);
        //var_dump($bid_html_body);

        //Storage::disk('local')->put('attachments/test1.html',$content);
        return;
    }

    public function getHtmlData($i) {
        if ($i == 4) return $i;
        return null;
    }
}
