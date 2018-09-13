<?php namespace App\Services\Spiders\Wechat;
use App\Events\Frontend\System\SystemNotify;
use App\Models\Scraper\WechatMpInfo;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/13 下午1:40
 * @email:    hank.HuiWang@gmail.com
 */

class WechatSpider
{

    /**
     * @var QueryList
     */
    protected $ql;

    public function __construct()
    {
        $this->ql = QueryList::getInstance();
    }

    /**
     * 根据公众号id获取公众号信息
     * @param $wx_hao
     */
    public function getGzhInfo($wx_hao) {
        $request_url = 'http://weixin.sogou.com/weixin?query='.$wx_hao.'&_sug_type_=&_sug_=n&type=1&page=1&ie=utf8';
        for ($i=0;$i<4;$i++) {
            $ips = getProxyIps(1,'sogou');
            $ip = $ips[0];
            $content = $this->requestUrl($request_url,$ip);
            if ($content) {
                $html = $content->getHtml();
                if (!str_contains('用户您好，您的访问过于频繁，为确认本次访问为正常用户行为，需要您协助验证',$html)) {
                    break;
                } else {
                    deleteProxyIp($ip,'sogou');
                }
            }
        }
        $wechatid = $content->find('label[name=em_weixinhao]')->text();
        $img = $content->find('div.img-box > a')->children('img')->attr('src');
        $url = $content->find('div.img-box')->children('a')->attr('href');
        $name = $content->find('div.txt-box > p.tit')->children('a')->text();
        $description = $content->find('ul.news-list2 > li')->children('dl')->map(function ($item) {
            $dt = $item->find('dt')->text();
            var_dump($dt);
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
            'description' => $description[0]['description']??'',
            'company' => $description[1]['company']??'',
            'last_qunfa_id' => 0
        ];
        return $data;
    }

    public function getGzhArticles(WechatMpInfo $mpInfo) {
        for ($i=0;$i<4;$i++) {
            $ips = getProxyIps(1,'sogou');
            $ip = $ips[0];
            $content = $this->requestUrl($mpInfo->wz_url,$ip);
            if ($content) {
                $html = $content->getHtml();
                if (!$content->find('title')->text()) {
                    //说明链接已过期
                    $newData = $this->getGzhInfo($mpInfo->wx_hao);
                    if (empty($newData['name'])) {
                        event(new SystemNotify('微信公众号['.$mpInfo->wx_hao.']不存在'));
                        return;
                    }
                    $mpInfo->wz_url = $newData['url'];
                    $mpInfo->save();
                }elseif (!str_contains('用户您好，您的访问过于频繁，为确认本次访问为正常用户行为，需要您协助验证',$html)) {
                    break;
                } else {
                    deleteProxyIp($ip,'sogou');
                }
            }
        }
        $html = $content->getHtml();
        $pattern = "/var\s+msgList\s+=\s+(\{[\s\S]*?\});/is";
        $items = [];
        if (preg_match($pattern, $html, $matchs)) {
            if(isset($matchs[1]))
            {
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
                                break;
                            case 3:
                                //图片
                                continue;
                                break;
                            case 34:
                                // 音频
                                continue;
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
                                $item['copyright_stat'] = $app_msg_ext_info['copyright_stat'];
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
                                continue;
                                break;
                            case 62:
                                $item['cdn_videoid'] = $listdic['video_msg_ext_info']['cdn_videoid'];
                                $item['thumb'] = $listdic['video_msg_ext_info']['thumb'];
                                continue;
                                break;
                        }
                        $items[] = $item;
                    }
                }
            }
        }
        return $items;
    }

    protected function requestUrl($url,$ip) {
        try {
            $opts = [
                'proxy' => $ip,
                //Set the timeout time in seconds
                'timeout' => 10
            ];
            if (empty($ip)) unset($opts['proxy']);
            $content = $this->ql->get($url,null,$opts);
        } catch (\Exception $e) {
            app('sentry')->captureException($e,['url'=>$url,'proxy'=>$ip]);
            $content = null;
        }
        return $content;
    }

}