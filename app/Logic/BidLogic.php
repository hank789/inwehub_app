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

    public static function scraperSaveList($data, $ql2, $cookie, $ips, &$count) {
        if (empty($data['list'])) return false;
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
            $ip = $ips[rand(0,count($ips)-1)];
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
            sleep(rand(5,20));
            $cookies2 = Setting()->get('scraper_jianyu360_app_cookie','');
            $cookies2Arr = explode('||',$cookies2);
            $item['bid_html_body'] = '';
            if ($cookies2) {
                $content = $ql2->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($item, $cookies2Arr){
                    //$r->setMethod('POST');
                    $r->setUrl('https://www.jianyu360.com/jyapp/article/content/'.$item['_id'].'.html');
                    //$r->setTimeout(10000); // 10 seconds
                    //$r->setDelay(3); // 3 seconds
                    $r->setHeaders([
                        'Host'   => 'www.jianyu360.com',
                        'Referer'       => 'https://www.jianyu360.com/jyapp/jylab/mainSearch',
                        'Accept'    => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15G77',
                        'Cookie' => $cookies2Arr[rand(0,count($cookies2Arr)-1)]
                    ]);
                    return $r;
                },false,[
                    '--proxy' => $ip['ip'].':'.$ip['port'],
                    '--proxy-type' => 'http'
                ]);
                $info['source_url'] = $content->find('a.original')->href;
                $bid_html_body = $ql2->removeHead()->getHtml();
                $dom = new Dom();
                $dom->load($bid_html_body);
                $html = $dom->find('pre#h_content');
                try {
                    $item['bid_html_body'] = $html->__toString();
                } catch (\Exception $e) {

                }
            }

            if (empty($info['source_url']) || empty($item['bid_html_body'])) {
                event(new SystemNotify('抓取招标详情失败，对应app cookie已失效，请到后台设置',[]));
                $content = $ql2->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $r) use ($item, $cookie){
                    $r->setUrl('https://www.jianyu360.com/article/content/'.$item['_id'].'.html');
                    //$r->setTimeout(10000); // 10 seconds
                    //$r->setDelay(5); // 3 seconds
                    $r->setHeaders([
                        'Host'   => 'www.jianyu360.com',
                        'Referer'       => 'https://www.jianyu360.com/jylab/supsearch/index.html',
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
                        'Cookie' => $cookie[rand(0,count($cookie)-1)]
                    ]);
                    return $r;
                },false,[
                    '--proxy' => $ip['ip'].':'.$ip['port'],
                    '--proxy-type' => 'http'
                ]);
                $info['source_url'] = $content->find('a.com-original')->href;
                $item['bid_html_body'] = $content->find('div.com-detail')->htmls()->first();
                if (empty($info['source_url']) || empty($item['bid_html_body'])) {
                    event(new SystemNotify('抓取招标详情失败，对应www站点cookie已失效，请到后台设置',[]));
                    return false;
                }
            }
            $info['source_domain'] = parse_url($info['source_url'], PHP_URL_HOST);
            $info['detail'] = $item;
            BidInfoModel::create($info);
            $count ++;
        }
        return true;
    }

}