<?php namespace App\Services\Spiders\Wechat;
use App\Events\Frontend\System\ExceptionNotify;
use App\Jobs\ArticleToSubmission;
use App\Jobs\GetArticleBody;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Setting;
use App\Services\RateLimiter;
use Jaeger\GHttp;
use QL\Ext\PhantomJs;
use QL\QueryList;
use QL\Services\HttpService;

/**
 * @author: wanghui
 * @date: 2018/9/29 下午12:10
 * @email:    hank.HuiWang@gmail.com
 */

class MpSpider {
    /**
     * @var QueryList
     */
    protected $ql;

    protected $cookie;

    protected $token = '231254010';

    protected $mpUrl = 'https://mp.weixin.qq.com';

    public function __construct()
    {
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $this->cookie = Setting::get('scraper_wechat_gzh_cookie');
        $this->token = Setting::get('scraper_wechat_gzh_token');
    }


    public function getGzhInfo($wx_hao) {
        $url = $this->mpUrl.'/cgi-bin/searchbiz?action=search_biz&token='.$this->token.'&lang=zh_CN&f=json&ajax=1&random=0.930593749582243&query='.$wx_hao.'&begin=0&count=5';
        $data = $this->ql->get($url,null,[
            'cookies' => null,
            'headers' => [
                'Host'   => 'mp.weixin.qq.com',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language:' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie' => $this->cookie
            ]
        ])->getHtml();
        $dataArr = json_decode($data, true);
        if (isset($dataArr['total']) && $dataArr['total'] > 0) {
            $mpInfo = $dataArr['list'][0];
            return [
                'name' => $mpInfo['nickname'],
                'wechatid' => $wx_hao,
                'img' => $mpInfo['round_head_img'],
                'url' => '',
                'qrcode' => '',
                'description' => '',
                'company' => '',
                'last_qunfa_id' => 0,
                'fakeid' => $mpInfo['fakeid']
            ];
        } elseif ($dataArr['base_resp']['ret'] == 200013) {
            //抓取太频繁
            var_dump($dataArr);
            event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败:'.$data));
        } elseif ($dataArr['base_resp']['ret'] != 0) {
            var_dump($dataArr);
            event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败:'.$data));
        } else {
            event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败:'.$data));
        }
        return false;
    }


    public function getGzhArticles(WechatMpInfo $mpInfo) {
        $mp = $this->getGzhInfo($mpInfo->wx_hao);
        if (!$mp) {
            return false;
        }
        $url = $this->mpUrl.'/cgi-bin/appmsg?token='.$this->token.'&lang=zh_CN&f=json&ajax=1&random=0.5033763103689131&action=list_ex&begin=0&count=5&query=&fakeid='.urlencode($mp['fakeid']).'&type=9';
        $data =$this->ql->get($url,null,[
            'cookies' => null,
            'headers' => [
                'Host'   => 'mp.weixin.qq.com',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language:' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                'Cookie' => $this->cookie
            ]
        ])->getHtml();
        $dataArr = json_decode($data, true);
        if ($dataArr['app_msg_cnt']) {
            return $dataArr['app_msg_list'];
        } else {
            event(new ExceptionNotify('微信公众号['.$mpInfo->wx_hao.']文章抓取失败:'.$data));
        }
        return [];
    }

    /**
     * @param $url
     * @param null $args
     * @param array $otherArgs
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function getResponse($url,$args = null,$otherArgs = []) {
        $otherArgs = array_merge([
            'cookies' => HttpService::getCookieJar(),
            'verify' => false
        ],$otherArgs);
        is_string($args) && parse_str($args,$args);
        $args = array_merge([
            'verify' => false,
            'query' => $args,
            'headers' => [
                'referer' => $url,
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36'
            ]
        ],$otherArgs);
        $client = GHttp::getClient();
        return $client->request('GET', $url,$args);
    }

}