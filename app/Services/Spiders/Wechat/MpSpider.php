<?php namespace App\Services\Spiders\Wechat;
use App\Events\Frontend\System\ExceptionNotify;
use App\Models\Scraper\WechatMpInfo;
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

    protected $mpAutoLogin;

    public function __construct()
    {
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $this->cookie = Setting::get('scraper_wechat_gzh_cookie');
        $this->token = Setting::get('scraper_wechat_gzh_token');
        $this->mpAutoLogin = new MpAutoLogin();
    }


    public function getGzhInfo($wx_hao, $waitScan = true) {
        $limit = RateLimiter::instance()->getValue('scraper_mp_freq',date('Y-m-d'));
        if ($limit) return false;
        $scraper_mp_count = RateLimiter::instance()->getValue('scraper_mp_count',date('Ymd'));
        if ($scraper_mp_count >= 102) return false;
        $url = $this->mpUrl.'/cgi-bin/searchbiz?action=search_biz&token='.$this->token.'&lang=zh_CN&f=json&ajax=1&random=0.930593749582243&query='.$wx_hao.'&begin=0&count=5';
        $args = [
            'headers' => [
                'Host'   => 'mp.weixin.qq.com',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language:' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                //'Cookie' => $this->cookie
            ]
        ];
        if ($this->cookie) {
            $args['cookies'] = null;
            $args['headers']['Cookie'] = $this->cookie;
        }
        $data = $this->ql->get($url,null,$args)->getHtml();
        $dataArr = json_decode($data, true);
        if (isset($dataArr['total']) && $dataArr['total'] > 0) {
            $mpInfo = $dataArr['list'][0];
            RateLimiter::instance()->increase('scraper_mp_count',date('Ymd'),60*60*24);
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
            \Log::info('mpspider',$dataArr);
            event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败,已抓取'.RateLimiter::instance()->getValue('scraper_mp_count',date('Ymd')).'次:'.$data));
            RateLimiter::instance()->setVale('scraper_mp_freq',date('Y-m-d'),1,60*60*24);
        } elseif ($dataArr['base_resp']['ret'] != 0 && $waitScan) {
            \Log::info('mpspider',$dataArr);
            $this->mpAutoLogin->setToken('');
            $this->cookie = '';
            $res = $this->mpAutoLogin->init([
                'account' => 'fan.pang@inwehub.com',
                'password' => 'HW(CP8LJU/',
                'key' => 'wechatmp'
            ]);
            if ($res) {
                $this->token = Setting::get('scraper_wechat_gzh_token');
                $cookie = HttpService::getCookieJar()->toArray();
                $cookieStr = '';
                foreach ($cookie as $val) {
                    $cookieStr .=$val['Name'].'='.$val['Value'].';';
                }
                Setting::set('scraper_wechat_gzh_cookie',$cookieStr);

                return $this->getGzhInfo($wx_hao);
            }
            event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败:'.$data));
        } elseif ($dataArr['base_resp']['ret'] == 0 && $dataArr['total'] <= 0) {
            event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败:'.$data));
            return -1;
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
        if ($mp == -1) {
            return [];
        }
        $url = $this->mpUrl.'/cgi-bin/appmsg?token='.$this->token.'&lang=zh_CN&f=json&ajax=1&random=0.5033763103689131&action=list_ex&begin=0&count=5&query=&fakeid='.urlencode($mp['fakeid']).'&type=9';
        $args = [
            'headers' => [
                'Host'   => 'mp.weixin.qq.com',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'Accept-Language:' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                //'Cookie' => $this->cookie
            ]
        ];
        if ($this->cookie) {
            $args['cookies'] = null;
            $args['headers']['Cookie'] = $this->cookie;
        }
        $data =$this->ql->get($url,null,$args)->getHtml();
        $dataArr = json_decode($data, true);
        if (isset($dataArr['app_msg_cnt'])) {
            return $dataArr['app_msg_list'];
        } else {
            event(new ExceptionNotify('微信公众号['.$mpInfo->wx_hao.']文章抓取失败:'.$data));
        }
        return false;
    }

    public function refreshCookie() {
        return $this->ql->get($this->mpUrl.'/cgi-bin/home?t=home/index&token='.$this->token.'&lang=zh_CN',null,[
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