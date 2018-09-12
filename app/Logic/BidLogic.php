<?php namespace App\Logic;
/**
 * @author: wanghui
 * @date: 2018/9/6 下午8:31
 * @email:    hank.HuiWang@gmail.com
 */
use App\Events\Frontend\System\SystemNotify;
use App\Models\Scraper\BidInfo as BidInfoModel;
use PHPHtmlParser\Dom;

class BidLogic {

    public static function scraperSaveList($data, $ql2, $cookiesPcArr, $cookiesAppArr, &$count) {
        if (empty($data['list'])) return false;
        $timeCost = 6;
        foreach ($data['list'] as $item) {
            var_dump($item['title']);
            //超过2天的不抓取
            if (isset($item['publishtime']) && $item['publishtime'] <= strtotime('-2 days')) {
                return false;
            }
            $bid = BidInfoModel::where('guid',$item['_id'])->first();
            if ($bid) {
                continue;
            }
            $startTime = time();
            $newBidIds[] = $item['_id'];
            $info = [
                'guid' => $item['_id'],
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
                'status' => 2,
                'source_url' => '',
            ];
            if ($timeCost <= 5) {
                sleep(rand(5-$timeCost,10-$timeCost));
            }
            $timeCost = 0;
            $bid_html_body = '';
            $item['bid_html_body'] = '';
            if ($cookiesAppArr) {
                for ($i=0;$i<3;$i++) {
                    $ips = getProxyIps(1);
                    $ip = $ips[0];
                    $content = self::getAppData($ql2,$item,$cookiesAppArr,$ip);
                    if ($content) {
                        $bid_html_body = $content->removeHead()->getHtml();
                        if ($bid_html_body != '<html></html>') {
                            break;
                        } else {
                            deleteProxyIp($ip);
                            getProxyIps(2);
                        }
                    }
                }
                $info['source_url'] = $content->find('a.original')->href;
                $dom = new Dom();
                $dom->load($bid_html_body);
                $html = $dom->find('pre#h_content');
                try {
                    $item['bid_html_body'] = $html->__toString();
                } catch (\Exception $e) {
                    \Log::info('scraper_jianyu_app_error',['bid_html_body'=>$bid_html_body, 'item'=>$item]);
                    app('sentry')->captureException($e,['bid_html_body'=>$bid_html_body, 'item'=>$item]);
                }
            }

            if (empty($info['source_url']) || empty($item['bid_html_body'])) {
                $fields = [];
                $fields[] = [
                    'title'=>'source_url',
                    'value'=>$info['source_url']
                ];
                $fields[] = [
                    'title'=>'item',
                    'value'=>json_encode($item)
                ];
                event(new SystemNotify('抓取招标详情失败，对应app cookie已失效，请到后台设置',$fields));
                sleep(rand(2,5));
                for ($i=0;$i<3;$i++) {
                    $ips = getProxyIps(1);
                    $ip = $ips[0];
                    $content = self::getPcData($ql2,$item,$cookiesPcArr,$ip);
                    if ($content) {
                        if ($content->getHtml() != '<html></html>') {
                            break;
                        } else {
                            deleteProxyIp($ip);
                            getProxyIps(2);
                        }
                    }
                }
                if ($content->getHtml() == '<html></html>') {
                    event(new SystemNotify('代理已耗尽，需重新申请',$fields));
                    return false;
                }
                $info['source_url'] = $content->find('a.com-original')->href;
                $item['bid_html_body'] = $content->find('div.com-detail')->htmls()->first();
                if (empty($info['source_url']) || empty($item['bid_html_body'])) {
                    \Log::info('scraper_jianyu_www_error',['bid_html_body'=>$content->getHtml(), 'item'=>$item]);
                    event(new SystemNotify('抓取招标详情失败，对应www站点cookie已失效，请到后台设置',$fields));
                    return false;
                }
            }
            $info['source_domain'] = parse_url($info['source_url'], PHP_URL_HOST);
            $info['detail'] = $item;
            try {
                $bid = BidInfoModel::where('guid',$item['_id'])->first();
                if ($bid) {
                    continue;
                }
                BidInfoModel::create($info);
                $count++;
            } catch (\Exception $e) {
                app('sentry')->captureException($e,['item'=>$item]);
            }
            $endTime = time();
            $timeCost = $endTime - $startTime;
        }
        return true;
    }

    public static function getAppData($ql2,$item,$cookiesAppArr,$ip) {
        $cookie = $cookiesAppArr[rand(0,count($cookiesAppArr)-1)];
        try {
            $content = $ql2->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($item, $cookie, $ip){
                //$r->setMethod('POST');
                $r->setUrl('https://www.jianyu360.com/jyapp/article/content/'.$item['_id'].'.html');
                $r->setTimeout(20000); // 10 seconds
                //$r->setDelay(3); // 3 seconds
                $r->setHeaders([
                    'Host'   => 'www.jianyu360.com',
                    'Referer'       => 'https://www.jianyu360.com/jyapp/jylab/mainSearch',
                    'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15G77',
                    'Cookie' => $cookie
                ]);
                return $r;
            },false,[
                '--proxy' => $ip,
                '--proxy-type' => 'http'
            ]);
        } catch (\Exception $e) {
            deleteProxyIp($ip);
            app('sentry')->captureException($e,['item'=>$item,'cookieApp'=>$cookie,'proxy'=>$ip]);
            $content = null;
        }
        return $content;
    }

    public static function getPcData($ql2,$item,$cookiesPcArr,$ip) {
        $cookie = $cookiesPcArr[rand(0,count($cookiesPcArr)-1)];
        try {
            $content = $ql2->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($item, $cookie, $ip){
                $r->setUrl('https://www.jianyu360.com/article/content/'.$item['_id'].'.html');
                $r->setTimeout(20000); // 10 seconds
                //$r->setDelay(5); // 3 seconds
                $r->setHeaders([
                    'Host'   => 'www.jianyu360.com',
                    'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                    'Cookie' => $cookie
                ]);
                return $r;
            },false,[
                '--proxy' => $ip,
                '--proxy-type' => 'http'
            ]);
        } catch (\Exception $e) {
            deleteProxyIp($ip);
            app('sentry')->captureException($e,['item'=>$item,'cookiePc'=>$cookie,'proxy'=>$ips[0]]);
            $content = null;
        }
        return $content;
    }

}