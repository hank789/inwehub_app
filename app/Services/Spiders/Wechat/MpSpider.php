<?php namespace App\Services\Spiders\Wechat;
use App\Events\Frontend\System\ExceptionNotify;
use App\Jobs\ArticleToSubmission;
use App\Jobs\GetArticleBody;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
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

    protected $token = '1932075981';

    protected $mpUrl = 'https://mp.weixin.qq.com';

    public function __construct()
    {
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $this->cookie = 'pgv_pvi=4037293056; RK=qJF7y/p/d2; tvfe_boss_uuid=7fa1a739b881e05c; hjstat_uv=34071019083032313781|679544; _mta_hide_nodata_dialog=1; ptcz=d622356dcfe8ab11cbae340f639da3d599c02cfc7f9a59b494a857b0c7633bc4; pgv_pvid=2501266948; ts_uid=3827688534; ua_id=33L3kGVDiZtL00bnAAAAAHyfE8AIygU9w1Dkbe7Kilg=; mm_lang=zh_CN; _ga=GA1.2.379491731.1522652529; sd_userid=13481525918588007; sd_cookie_crttime=1525918588007; eas_sid=r1U5A2q9b1W3A8V052f7f7f0R0; o_cookie=503849201; pac_uid=1_503849201; pt2gguin=o0503849201; rewardsn=; wxtokenkey=777; pgv_si=s8129298432; cert=VbQPAQN_BUjvxPKF6XyTUzcx92RB3MkK; noticeLoginFlag=1; uuid=4ebf887c500b20fbd18ff15f16d48250; ticket=0e698bcbd9f01cc0f850bd0e64ed10ec5f771a53; ticket_id=gh_91611cb14414; remember_acct=fan.pang%40inwehub.com; data_bizuin=3249858999; bizuin=3552079191; data_ticket=MUwgutmKhauDpKCoVe6er5vVjIqcqjwfmwruJWFoNGIAa81S4Vlki4dYTiSgkSji; slave_sid=VzRwc2ZJaVI3TFBVY0lheXVPcE45UDFyd0VhdkZrV0MwSmtYMnRWWWRjUjE3NU00Y0tMbndZVXBzRUlYMnFWRUlfMGYzWXZvNktZbDkyQnk1bExPeTczdWxLRjB2UXA5clhCX3lSeWxOak85Q21WU0lCbDBQbFRPeUM0UVBxUEJjNzA0U3lzS3Y2bVA5aE9N; slave_user=gh_91611cb14414; xid=6ae9b9ef6b546eb8e67737409982647f; openid2ticket_ot-m0wdarA6XDnPow76o0Dw2bFNA=a6FiOsi3I64n86cF51uioLMYGGIYusmwGFwN0aYHPE8=';
    }


    public function getGzhInfo($wx_hao) {
        $url = $this->mpUrl.'/cgi-bin/searchbiz?action=search_biz&token='.$this->token.'&lang=zh_CN&f=json&ajax=1&random=0.930593749582243&query='.$wx_hao.'&begin=0&count=5';
        $data = $this->ql->get($url,null,[
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
        if ($dataArr['total'] > 0) {
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