<?php namespace App\Console\Commands\Scraper;
use App\Jobs\ArticleToSubmission;
use App\Jobs\GetArticleBody;
use App\Jobs\UpdateProductInfoCache;
use App\Logic\TaskLogic;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\WechatSogouSpider;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * @author: wanghui
 * @date: 2017/4/13 下午7:42
 * @email: hank.huiwang@gmail.com
 */


class WechatPosts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:posts {gzh?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取微信公众号文章';
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
        $path = config('app.spider_path');
        if($path){
            $domain = 'sogou';
            $members = RateLimiter::instance()->sMembers('proxy_ips_deleted_'.$domain);
            foreach ($members as $member) {
                deleteProxyIp($member,$domain);
            }
            validateProxyIps($domain);
            //shell_exec('cd '.$path.' && python updatemp.py >> /tmp/updatemp.log');
            getProxyIps(5,$domain);
            $spider = new WechatSogouSpider();
            $gzh = $this->argument('gzh');
            $notify = true;
            if ($gzh) {
                $notify = false;
                $mpInfos = WechatMpInfo::where('status',1)->where('wx_hao',$gzh)->orderBy('update_time','asc')->get();
            } else {
                $mpInfos = WechatMpInfo::where('status',1)->where('rank_article_release_count','>=',0)->orderBy('update_time','asc')->get();
            }
            $succ_count = 0;
            foreach ($mpInfos as $mpInfo) {
                $this->info($mpInfo->name);
                //一个小时内刚处理过的跳过
                if (strtotime($mpInfo->update_time) >= strtotime('-90 minutes')) continue;
                $todayCount = WechatWenzhangInfo::where('source_type',1)->where('mp_id',$mpInfo->_id)->where('date_time','>=',date("Y-m-d 00:00:00"))->count();
                if ($todayCount >= 1) continue;
                #查看一下该号今天是否已经发送文章
                $last_qunfa_id = $mpInfo->last_qunfa_id;
                $last_qunfa_time = $mpInfo->last_qufa_time;
                $cur_qunfa_id = $last_qunfa_id;
                $wz_list = $spider->getGzhArticles($mpInfo);
                if ($wz_list === false) break;
                if (count($wz_list) <= 0) continue;
                $qunfa_time = '';
                foreach ($wz_list as $wz_item) {
                    $temp_qunfa_id = $wz_item['qunfa_id'];
                    if ($last_qunfa_id >= $temp_qunfa_id) {
                        $this->info('没有更新文章');
                        break;
                    }
                    if ($cur_qunfa_id < $temp_qunfa_id) {
                        $cur_qunfa_id = $temp_qunfa_id;
                        $qunfa_time = date('Y-m-d H:i:s',$wz_item['datetime']);
                    }
                    $succ_count += 1;
                    if ($wz_item['type'] == 49) {
                        if (empty($wz_item['content_url'])) continue;
                        $this->info($wz_item['title']);
                        $wz_item['title'] = formatHtml($wz_item['title']);
                        $wz_item['digest'] = formatHtml($wz_item['digest']);
                        $uuid = base64_encode($mpInfo->_id.$wz_item['title'].date('Y-m-d',$wz_item['datetime']));
                        if (RateLimiter::instance()->hGet('wechat_article',$uuid)) continue;
                        $article = WechatWenzhangInfo::create([
                            'title' => $wz_item['title'],
                            'source_url' => $wz_item['source_url'],
                            'content_url' => $wz_item['content_url'],
                            'cover_url'   => $wz_item['cover'],
                            'description' => $wz_item['digest'],
                            'date_time'   => date('Y-m-d H:i:s',$wz_item['datetime']),
                            'mp_id' => $mpInfo->_id,
                            'author' => $wz_item['author'],
                            'msg_index' => $wz_item['main'],
                            'copyright_stat' => $wz_item['copyright_stat'],
                            'qunfa_id' => $wz_item['qunfa_id'],
                            'type' => $wz_item['type'],
                            'like_count' => 0,
                            'read_count' => 0,
                            'comment_count' => 0
                        ]);
                        RateLimiter::instance()->hSet('wechat_article',$uuid,$article->_id);
                        $article->addProductTag();
                        (new GetArticleBody($article->_id))->handle();
                        if ($mpInfo->is_auto_publish == 1 && $article->date_time >= date('Y-m-d 00:00:00',strtotime('-1 days'))) {
                            dispatch(new ArticleToSubmission($article->_id));
                        }
                    }
                }
                if ($last_qunfa_id < $cur_qunfa_id) {
                    $mpInfo->last_qunfa_id = $cur_qunfa_id;
                    $mpInfo->last_qufa_time = $qunfa_time;
                }
                $mpInfo->update_time = date('Y-m-d H:i:s');
                $mpInfo->save();
                sleep(rand(5,15));
            }
            $articles = WechatWenzhangInfo::where('source_type',1)->where('topic_id',0)->where('status',1)->where('date_time','>=',date('Y-m-d 00:00:00',strtotime('-1 days')))->get();
            if (Setting()->get('is_scraper_wechat_auto_publish',1)) {
                $second = 0;
                foreach ($articles as $article) {
                    if ($second > 0) {
                        dispatch(new ArticleToSubmission($article->_id))->delay(Carbon::now()->addSeconds($second));
                    } else {
                        dispatch(new ArticleToSubmission($article->_id));
                    }
                    $second += 300;
                }
            } else {
                $count = count($articles);
                if ($count > 0 && $notify) {
                    TaskLogic::alertManagerPendingArticles($count);
                }
            }
            //更新产品信息缓存
            $ids = RateLimiter::instance()->hGetAll('product_pending_update_cache');
            if ($ids) {
                foreach ($ids as $key=>$val) {
                    dispatch_now(new UpdateProductInfoCache($key));
                    RateLimiter::instance()->hDel('product_pending_update_cache',$key);
                }
            }
        }
    }

}