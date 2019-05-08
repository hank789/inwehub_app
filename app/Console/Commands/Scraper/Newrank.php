<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\ExceptionNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\ArticleToSubmission;
use App\Jobs\NewSubmissionJob;
use App\Logic\TaskLogic;
use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;

/**
 * @author: wanghui
 * @date: 2018/9/19 下午4:27
 * @email:    hank.HuiWang@gmail.com
 */

class Newrank extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:newrank:wechat';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取新榜微信公众号信息';

    protected $ql;

    protected $auth;

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
        return;
        $mpInfos = WechatMpInfo::where('status',1)->orderBy('update_time','asc')->get();
        $this->ql = QueryList::getInstance();
        $this->getAuth();

        foreach ($mpInfos as $mpInfo) {
            $this->info($mpInfo->name);
            //一个小时内刚处理过的跳过
            //if (strtotime($mpInfo->update_time) >= strtotime('-120 minutes')) continue;
            if (strlen($mpInfo->newrank_id) < 30) {
                $info = $this->getMpInfo($mpInfo->wx_hao);
                if (isset($info['uuid'])) {
                    $mpInfo->newrank_id = $info['uuid'];
                    $mpInfo->save();
                } elseif (empty($info)){
                    $this->clearAuth();
                    $this->getAuth();
                    $info = $this->getMpInfo($mpInfo->wx_hao);
                    if (isset($info['uuid'])) {
                        $mpInfo->newrank_id = $info['uuid'];
                        $mpInfo->save();
                    } else {
                        event(new ExceptionNotify('获取新榜微信号信息失败:'.$mpInfo->wx_hao));
                        continue;
                    }
                } else {
                    continue;
                }
            }
            $data = [
                'uuid' => $mpInfo->newrank_id,
                'flag' => true
            ];
            $list = $this->getListData($data);
            if (!isset($list['value']['lastestArticle'])) {
                $this->clearAuth();
                $this->getAuth();
                $list = $this->getListData($data);
            }
            if (!isset($list['value']['lastestArticle'])) {
                event(new ExceptionNotify('获取新榜微信号文章失败:'.$mpInfo->wx_hao));
                return;
            }
            foreach ($list['value']['lastestArticle'] as $wz_item) {
                $this->info($wz_item['title']);
                //if (strtotime($wz_item['publicTime']) <= strtotime('-2 days')) continue;
                $wz_item['title'] = formatHtml($wz_item['title']);
                $wz_item['summary'] = formatHtml($wz_item['summary']);
                $uuid = base64_encode($mpInfo->_id.$wz_item['title'].date('Y-m-d',strtotime($wz_item['publicTime'])));
                $exit = RateLimiter::instance()->hGet('wechat_article',$uuid);
                if ($exit) {
                    $exitArticle = WechatWenzhangInfo::find($exit);
                    if ($exitArticle) {
                        if (str_contains($exitArticle->content_url,'wechat_redirect') || str_contains($exitArticle->content_url,'__biz=') || str_contains($exitArticle->content_url,'/s/')) {
                            continue;
                        }
                        $exitArticle->content_url = $wz_item['url'];
                        $exitArticle->save();
                        if ($exitArticle->topic_id > 0) {
                            $submission = Submission::find($exitArticle->topic_id);
                            if ($submission) {
                                $data = $submission->data;
                                $data['url'] = $wz_item['url'];
                                $submission->data = $data;
                                $submission->save();
                            }
                        }
                    }
                    continue;
                }
                if (strtotime($wz_item['publicTime']) <= strtotime('-2 days')) continue;
                $exist_submission_id = Redis::connection()->hget('voten:submission:url',$wz_item['url']);
                if ($exist_submission_id) continue;
                $article = WechatWenzhangInfo::create([
                    'title' => $wz_item['title'],
                    'source_url' => '',//此api接口内应该是唯一的
                    'content_url' => $wz_item['url'],
                    'cover_url'   => '',
                    'description' => $wz_item['summary'],
                    'date_time'   => $wz_item['publicTime'],
                    'mp_id' => $mpInfo->_id,
                    'author' => $wz_item['author']??'',
                    'msg_index' => 1,
                    'copyright_stat' => $wz_item['copyright_stat']??100,
                    'qunfa_id' => 0,
                    'type' => $wz_item['type']??49,
                    'like_count' => $wz_item['likeCount']?:0,
                    'read_count' => $wz_item['clicksCount']?:0,
                    'comment_count' => $wz_item['commentsCount']?:0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$uuid,$article->_id);
                if ($mpInfo->is_auto_publish == 1) {
                    dispatch(new ArticleToSubmission($article->_id));
                }
            }
        }

    }

    protected function getListData($data,$count=0) {
        sleep(rand(6,15));
        $headers = [
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Referer' => 'https://www.newrank.cn/public/info/detail.html?account=fesco-bj',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'cookie' => 'tt_token=true; rmbuser=true; name=15050368286; useLoginAccount=true; token='.$this->auth.'; __root_domain_v=.newrank.cn;'
        ];
        $requestUrl = 'https://www.newrank.cn/xdnphb/detail/getAccountArticle';
        $nonce = $this->getNonce();
        $xyz = $this->getXyz('/xdnphb/detail/getAccountArticle',$data,$nonce);
        $data['nonce'] = $nonce;
        $data['xyz'] = $xyz;

        try {
            $content = $this->ql->post($requestUrl,$data,[
                'timeout' => 60,
                'headers' => $headers
            ])->getHtml();
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            $content = null;
        }

        $count++;
        if (empty($content) && $count <= 3) {
            unset($data['nonce']);
            unset($data['xyz']);
            return $this->getListData($data,$count);
        }
        var_dump($content);
        return json_decode($content, true);
    }

    protected function clearAuth() {
        RateLimiter::instance()->setVale('newrank','token','',60);
    }

    protected function getAuth() {
        $auth = RateLimiter::instance()->getValue('newrank','token');
        if (!$auth) {
            sleep(1);
            $flag = substr(str_replace('.','',microtime(true)),0,13);
            $nonce = $this->getNonce();
            $data = [
                'username' => "15050368286",
                'password' => md5(md5('hank8831').'daddy'),
                'identifyCode' => '',
                'flag' => $flag.(float)rand()/(float)getrandmax().rand(10,99)
            ];
            $xyz = $this->getXyz('/xdnphb/login/new/usernameLogin',$data,$nonce);
            $data['nonce'] = $nonce;
            $data['xyz'] = $xyz;
            $result = $this->ql->post('https://www.newrank.cn/xdnphb/login/new/usernameLogin',$data,[
                'headers'=>[
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'accept-encoding' => 'gzip, deflate, br',
                    'accept-language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
                    'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                    'cookie' => 'tt_token=true; rmbuser=true; name=15050368286; useLoginAccount=true; __root_domain_v=.newrank.cn',
                    'Origin' => 'https://www.newrank.cn',
                    'Referer' => 'https://www.newrank.cn/public/login/login.html?back=https%3A//www.newrank.cn/',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
                    'X-Requested-With' => 'XMLHttpRequest'
                ]
            ])->getHtml();
            var_dump($result);
            $resultArr = json_decode($result,true);
            //var_dump($resultArr);
            $auth = $resultArr['value']['token'];
            //var_dump($auth);
            RateLimiter::instance()->setVale('newrank','token',$auth,60*60*24*5);
        }
        $this->auth = $auth;
    }

    public function getMpInfo($wxhao) {
        sleep(3);
        $headers = [
            'content-type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Referer' => 'https://www.newrank.cn/public/info/detail.html?account=fesco-bj',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7,pl;q=0.6',
            'upgrade-insecure-requests' => 1,
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
            'cookie' => 'tt_token=true; rmbuser=true; name=15050368286; useLoginAccount=true; token='.$this->auth.'; __root_domain_v=.newrank.cn;'
        ];
        try {
            $content = $this->ql->get('https://www.newrank.cn/public/info/detail.html',[
                'account' => $wxhao
            ],[
                'timeout' => 30,
                'headers' => $headers
            ]);
            $title = $content->find('title')->text();
            if (str_contains($title,'页面错误')) {
                var_dump('页面错误');
                return -1;
            }
            $result = $content->getHtml();
            $pattern = "/var\s+fgkcdg\s+=\s+(\{[\s\S]*?\});/is";
            preg_match($pattern, $result, $matchs);
            if (isset($matchs[1]) && $matchs[1]) {
                $matchs[1] = formatHtml($matchs[1]);
                $data = json_decode($matchs[1],true);
                var_dump($data);
            } else {
                return false;
            }
            return $data;
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
        }
        return false;
    }

    public function getNonce() {
        $nonce = '';
        $a = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"];
        for ($i=0;$i<9;$i++) {
            $nonce .= $a[rand(0,15)];
        }
        return $nonce;
    }

    public function getXyz($urlPath,$data,$nonce) {
        $xyz = $urlPath.'?AppKey=joker';
        $keys = array_keys($data);
        sort($keys);
        foreach ($keys as $key) {
            $xyz .= '&'.$key.'='.$data[$key];
        }
        $xyz .= '&nonce='.$nonce;
        //var_dump($xyz);
        return md5($xyz);
    }
}