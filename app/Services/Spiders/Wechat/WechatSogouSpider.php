<?php namespace App\Services\Spiders\Wechat;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Models\Scraper\WechatMpInfo;
use App\Services\RuoKuaiService;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/13 下午1:40
 * @email:    hank.HuiWang@gmail.com
 */

class WechatSogouSpider
{

    /**
     * @var QueryList
     */
    protected $ql;

    protected $url;

    protected $proxyIp;

    protected $ssIpLocked = false;

    protected $snuid = '';

    protected $client = '';

    protected $cookieJar = '';

    public function __construct()
    {
        $this->ql = QueryList::getInstance();
        $this->client = new Client(['cookies' => true,'verify' => false]);
    }

    /**
     * 根据公众号id获取公众号信息
     * @param $wx_hao
     */
    public function getGzhInfo($wx_hao) {
        $request_url = 'http://weixin.sogou.com/weixin?query='.$wx_hao.'&_sug_type_=&_sug_=n&type=1&page=1&ie=utf8';
        $jieFengCount = 0;
        $jfResult = false;
        for ($i=0;$i<16;$i++) {
            $ips = getProxyIps(5,'sogou');
            $ip = $ips[0]??'';
            if ($i>=14) $ip =null;
            var_dump($ip);
            if ($jfResult) {
                $request_url = 'http://weixin.sogou.com/weixin?type=2&query='.$wx_hao.'&ie=utf8&s_from=input&_sug_=n&_sug_type_=1&w=01015002&oq=&ri=0&sourceid=sugg&sut=0&sst0=1547216885721&lkt=0,0,0&p=40040108';
            }
            $content = $this->requestUrl($request_url,$ip);
            if ($content) {
                $sogouTitle = $content->find('title')->text();
                if (str_contains($sogouTitle,$wx_hao)) {
                    var_dump('抓取公众号成功');
                    break;
                } else {
                    var_dump('公众号访问频繁');
                    $r = $content->find('input[name=r]')->val();
                    $jfResult = $this->jiefeng($r);
                    if ($jieFengCount >= 2) {
                        event(new ExceptionNotify('微信公众号['.$wx_hao.']抓取失败，无法解封IP'));
                        exit();
                    }
                    $jieFengCount ++;
                    deleteProxyIp($ip,'sogou');
                }
            } else {
                deleteProxyIp($ip,'sogou');
            }
        }
        $wechatid = $content->find('label[name=em_weixinhao]')->eq(0)->text();
        $img = $content->find('div.img-box > a')->children('img')->attr('src');
        $url = $content->find('div.img-box')->children('a')->attr('href');
        $name = $content->find('div.txt-box > p.tit')->eq(0)->children('a')->text();
        $description = $content->find('ul.news-list2 > li')->children('dl')->map(function ($item) {
            $dt = $item->find('dt')->text();
            if ($dt == '功能介绍：') {
                return ['description'=>$item->find('dd')->text()];
            }
            if (str_contains($dt,'认证：')) {
                return ['company'=>$item->find('dd')->text()];
            }
        })->toArray();
        $data = [
            'name' => $name,
            'wechatid' => $wechatid,
            'img' => $img,
            'url' => $url,
            'qrcode' => '',
            'description' => $description[0]['description']??'',
            'company' => $description[1]['company']??'',
            'last_qunfa_id' => 0
        ];
        return $data;
    }

    public function getGzhArticles(WechatMpInfo $mpInfo) {
        for ($i=0;$i<16;$i++) {
            $ips = getProxyIps(5,'sogou');
            var_dump($ips);
            $ip = $ips[0]??'';
            if ($i>=14) $ip =null;
            var_dump($ip);
            if (empty($mpInfo->wz_url)) {
                $newData = $this->getGzhInfo($mpInfo->wx_hao);
                if (empty($newData['name'])) {
                    event(new ExceptionNotify('微信公众号['.$mpInfo->wx_hao.']不存在'));
                    return [];
                }
                $mpInfo->wz_url = $newData['url'];
                $mpInfo->save();
            }
            $content = $this->requestUrl($mpInfo->wz_url,$ip);
            if ($content) {
                $sogouTitle = $content->find('title')->text();
                if (str_contains($sogouTitle,'请输入验证码')) {
                    var_dump('请输入验证码');
                    if (empty($ip) && !$this->ssIpLocked) {
                        $wzHtml = curlShadowsocks($mpInfo->wz_url);
                        $content->setHtml($wzHtml);
                        $sogouTitle = $content->find('title')->text();
                        if (!$sogouTitle) {
                            var_dump('链接已过期');
                            //说明链接已过期
                            $newData = $this->getGzhInfo($mpInfo->wx_hao);
                            if (empty($newData['name'])) {
                                event(new ExceptionNotify('微信公众号['.$mpInfo->wx_hao.']不存在'));
                                return [];
                            }
                            $mpInfo->wz_url = $newData['url'];
                            $mpInfo->save();
                            continue;
                        } elseif (!str_contains($sogouTitle,'请输入验证码')) {
                            var_dump('Shadowsocks抓取文章列表成功');
                            break;
                        } else {
                            var_dump('Shadowsocks需要验证码');
                            $jiefengR = $this->jiefeng2(true);
                            if ($jiefengR && $jiefengR['ret'] != -6) {
                                continue;
                            }
                            $this->ssIpLocked = true;
                        }
                    }
                    $jiefengR = $this->jiefeng2();
                    if ($jiefengR && $jiefengR['ret'] == -6) {
                        event(new ExceptionNotify('微信公众号['.$mpInfo->wx_hao.']抓取文章失败，无法解封IP'));
                        return false;
                    }
                    deleteProxyIp($ip,'sogou');
                } elseif (!$sogouTitle) {
                    var_dump('链接已过期');
                    //说明链接已过期
                    $newData = $this->getGzhInfo($mpInfo->wx_hao);
                    if (empty($newData['name'])) {
                        event(new ExceptionNotify('微信公众号['.$mpInfo->wx_hao.']不存在'));
                        return [];
                    }
                    $mpInfo->wz_url = $newData['url'];
                    $mpInfo->save();
                } elseif (str_contains($sogouTitle,$mpInfo->name)) {
                    var_dump('抓取文章列表成功');
                    break;
                } else {
                    deleteProxyIp($ip,'sogou');
                }
            } else {
                deleteProxyIp($ip,'sogou');
            }
        }
        $html = $content->getHtml();
        $pattern = "/var\s+msgList\s+=\s+(\{[\s\S]*?\});/is";
        $items = [];
        if (preg_match($pattern, $html, $matchs)) {
            if(isset($matchs[1]))
            {
                $matchs[1] = formatHtml($matchs[1]);
                $data = json_decode($matchs[1],true);
                if (isset($data['list'])) {
                    foreach ($data['list'] as $listdic) {
                        $item = [];
                        $comm_msg_info = $listdic['comm_msg_info'];
                        $item['qunfa_id'] = $comm_msg_info['id'];  # 不可判重，一次群发的消息的id是一样的
                        $item['datetime'] = $comm_msg_info['datetime'];
                        $item['type'] = $comm_msg_info['type'];
                        switch ($item['type']) {
                            case 1:
                                //文字
                                $item['content'] = $comm_msg_info['content'];
                                $items[] = $item;
                                break;
                            case 3:
                                //图片
                                break;
                            case 34:
                                // 音频
                                break;
                            case 49:
                                //图文
                                $app_msg_ext_info = $listdic['app_msg_ext_info'];
                                $url = $app_msg_ext_info['content_url']??'';
                                if($url && !str_contains($url,'http://mp.weixin.qq.com')) {
                                    $url = 'http://mp.weixin.qq.com'.$url;
                                }
                                $msg_index = 1;
                                $item['main'] = $msg_index;
                                $item['title'] = $app_msg_ext_info['title'];
                                $item['digest'] = $app_msg_ext_info['digest'];
                                $item['fileid'] = $app_msg_ext_info['fileid'];
                                $item['content_url'] = $url;
                                $item['source_url'] = $app_msg_ext_info['source_url'];
                                $item['cover'] = $app_msg_ext_info['cover'];
                                $item['author'] = $app_msg_ext_info['author'];
                                $item['copyright_stat'] = $app_msg_ext_info['copyright_stat']??0;
                                $items[] = $item;
                                if ($app_msg_ext_info['is_multi'] == 1) {
                                    foreach ($app_msg_ext_info['multi_app_msg_item_list'] as $multidic) {
                                        $url = $multidic['content_url']??'';
                                        if($url && !str_contains($url,'http://mp.weixin.qq.com')) {
                                            $url = 'http://mp.weixin.qq.com'.$url;
                                        }
                                        $itemnew = [];
                                        $itemnew['qunfa_id'] = $item['qunfa_id'];
                                        $itemnew['datetime'] = $item['datetime'];
                                        $itemnew['type'] = $item['type'];
                                        $msg_index += 1;
                                        $itemnew['main'] = $msg_index;
                                        $itemnew['title'] = $multidic['title'];
                                        $itemnew['digest'] = $multidic['digest'];
                                        $itemnew['fileid'] = $multidic['fileid'];
                                        $itemnew['content_url'] = $url;
                                        $itemnew['source_url'] = $multidic['source_url'];
                                        $itemnew['cover'] = $multidic['cover'];
                                        $itemnew['author'] = $multidic['author'];
                                        $itemnew['copyright_stat'] = $multidic['copyright_stat'];
                                        $items[] = $itemnew;
                                    }
                                }
                                break;
                            case 62:
                                $item['cdn_videoid'] = $listdic['video_msg_ext_info']['cdn_videoid'];
                                $item['thumb'] = $listdic['video_msg_ext_info']['thumb'];
                                //$items[] = $item;
                                break;
                        }
                    }
                }
            }
        } else {
            event(new ExceptionNotify('抓取微信公众号['.$mpInfo->wx_hao.']文章失败'));
            return false;
        }
        return $items;
    }

    protected function requestUrl($url,$ip) {
        try {
            $this->url = $url;
            $this->proxyIp = $ip;
            $opts = [
                'proxy' => $ip,
                //Set the timeout time in seconds
                'timeout' => 10
            ];
            if (empty($ip)) {
                unset($opts['proxy']);
            }
            $response = $this->client->get($url);
            $body = $response->getBody();
            $this->ql->setHtml((string) $body);
            return $this->ql;
            //$content = $this->ql->get($url,null,$opts);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            app('sentry')->captureException($e,['url'=>$url,'proxy'=>$ip]);
            $content = null;
        }
        return $content;
    }

    public function jiefeng($r) {
        $max_count = 1;
        if ($this->proxyIp) return false;
        print("出现验证码，准备自动识别");
        while ($max_count < 2) {
            $max_count += 1;
            $time = intval(microtime(true) * 1000);
            $codeurl = 'http://weixin.sogou.com/antispider/util/seccode.php?tc='.$time;
            $img_data = (string) $this->client->get($codeurl)->getBody();
            $result = RuoKuaiService::dama($img_data);
            if (isset($result['Result'])) {
                $img_code = $result['Result'];
                $post_data = [
                    'c' => $img_code,
                    'r' => $r,
                    'v' => 5
                ];

                $result = (string) $this->client->post('http://weixin.sogou.com/antispider/thank.php',['verify' => false,'form_params'=>$post_data])->getBody();
                var_dump($result);

                $resultArr = json_decode($result,true);
                if ($resultArr['code'] != 0) {
                    print("搜狗返回验证码错误，1秒后更换验证码再次启动尝试，尝试次数：".($max_count));
                    sleep(1);
                    continue;
                }
                if (isset($resultArr['id']) && $resultArr['id']) {
                    $pbsnuid = $resultArr['id'];
                    var_dump($pbsnuid);
                    $this->snuid = $pbsnuid;
                    $pburl = 'http://pb.sogou.com/pv.gif?uigs_productid=webapp&type=antispider&subtype=0_seccodeInputSuccess&domain=weixin&suv=&snuid='.$pbsnuid.'&t='.time();
                    $this->client->get($pburl);
                    // get cookie
                    $config = $this->client->getConfig();
                    $config['cookies']->setCookie(
                        new SetCookie([
                            'Name'     => 'SNUID',
                            'Value'    => $pbsnuid,
                            'Domain'   => 'weixin.sogou.com',
                            'Path'     => '/',
                            'Max-Age'  => null,
                            'Expires'  => null,
                            'Secure'   => false,
                            'Discard'  => false,
                            'HttpOnly' => false
                        ])
                    );
                    //var_dump($config['cookies']);
                    return true;
                }
            }
        }
        return false;
    }

    public function jiefeng2($proxy=false) {
        if ($this->proxyIp) return false;
        $time = explode(' ',microtime());
        $timever = $time[1].($time[0] * 1000);
        $codeurl = 'http://mp.weixin.qq.com/mp/verifycode?cert='.$timever;
        $img_data = (string)$this->client->get($codeurl)->getBody();
        $result = RuoKuaiService::dama($img_data,2040);
        $img_code = $result['Result'];
        $post_url = 'http://mp.weixin.qq.com/mp/verifycode';
        $post_data = [
            'cert' => $timever,
            'input'=> $img_code,
            'appmsg_token' => ''
        ];
        $otherArgs = [];
        if ($proxy) {
            $otherArgs = ['proxy' => 'socks5h://127.0.0.1:1080'];
        }
        $result2 = (string) $this->client->post($post_url,array_merge(['verify' => false,'form_params'=>$post_data],$otherArgs))->getBody();
        var_dump($result2);
        return json_decode($result2,true);
    }

}