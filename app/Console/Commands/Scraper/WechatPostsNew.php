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


class WechatPostsNew extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:posts-new {gzh?}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取微信公众号文章New';
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
                try {
                    $info = $spider->getGzhInfo($mpInfo->wx_hao,false,2);
                } catch (\Exception $e) {
                    break;
                }
                if ($info === false) break;
                if (empty($info['lastArticle'])) continue;

                if ($last_qunfa_time >= date('Y-m-d H:i:s',$info['lastArticle']['lastArticleTime'])) {
                    $this->info('没有更新文章');
                    break;
                }
                $this->info($info['lastArticle']['lastArticleTitle']);
                $uuid = base64_encode($mpInfo->_id.$info['lastArticle']['lastArticleTitle'].date('Y-m-d',$info['lastArticle']['lastArticleTime']));
                if (RateLimiter::instance()->hGet('wechat_article',$uuid)) continue;

                $article = WechatWenzhangInfo::create([
                    'title' => formatHtml($info['lastArticle']['lastArticleTitle']),
                    'source_url' => '',
                    'content_url' => $info['url'],
                    'cover_url'   => '',
                    'description' => $info['lastArticle']['lastArticleTitle'],
                    'date_time'   => date('Y-m-d H:i:s',$info['lastArticle']['lastArticleTime']),
                    'mp_id' => $mpInfo->_id,
                    'author' => '',
                    'msg_index' => 0,
                    'copyright_stat' => 0,
                    'qunfa_id' => 0,
                    'type' => 0,
                    'like_count' => 0,
                    'read_count' => 0,
                    'comment_count' => 0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$uuid,$article->_id);
                (new GetArticleBody($article->_id))->handle();
                $article->addProductTag();
                if ($article->date_time >= date('Y-m-d 00:00:00',strtotime('-1 days'))) {
                    dispatch(new ArticleToSubmission($article->_id));
                }


                if ($last_qunfa_id < $cur_qunfa_id) {
                    $mpInfo->last_qufa_time = date('Y-m-d H:i:s',$info['lastArticle']['lastArticleTime']);
                }
                $mpInfo->update_time = date('Y-m-d H:i:s');
                $mpInfo->save();
                sleep(rand(8,15));
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
                    dispatch(new UpdateProductInfoCache($key));
                    RateLimiter::instance()->hDel('product_pending_update_cache',$key);
                }
            }
        }
    }

}