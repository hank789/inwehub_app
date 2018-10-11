<?php namespace App\Console\Commands\Scraper;
use App\Jobs\ArticleToSubmission;
use App\Jobs\GetArticleBody;
use App\Logic\TaskLogic;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * @author: wanghui
 * @date: 2017/4/13 下午7:42
 * @email: hank.huiwang@gmail.com
 */


class WechatMpPosts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:gzh:posts';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取微信公众号文章(根据公众号平台抓)';
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
        $mpInfos = WechatMpInfo::where('status',1)->orderBy('update_time','asc')->get();
        $spider = new MpSpider();
        $successCount = 0;
        foreach ($mpInfos as $mpInfo) {
            $this->info($mpInfo->name);
            //一个小时内刚处理过的跳过
            if (strtotime($mpInfo->update_time) >= strtotime('-90 minutes')) continue;
            $wz_list = $spider->getGzhArticles($mpInfo);
            if ($wz_list === false || $successCount >= 50) {
                Artisan::call('scraper:wechat:posts');
                break;
            }
            $successCount++;
            foreach ($wz_list as $wz_item) {
                $this->info($wz_item['title']);
                if ($wz_item['update_time'] <= strtotime('-2 days')) continue;
                $uuid = base64_encode($wz_item['title'].$wz_item['digest']);
                if (RateLimiter::instance()->hGet('wechat_article',$uuid)) continue;
                $content_url = substr($wz_item['link'],0,strpos($wz_item['link'],'&chksm='));
                if (WechatWenzhangInfo::where('content_url',$content_url)->first()) continue;
                $article = WechatWenzhangInfo::create([
                    'title' => $wz_item['title'],
                    'source_url' => $wz_item['aid'],//此api接口内应该是唯一的
                    'content_url' => $content_url,
                    'cover_url'   => $wz_item['cover'],
                    'description' => $wz_item['digest'],
                    'date_time'   => date('Y-m-d H:i:s',$wz_item['update_time']),
                    'mp_id' => $mpInfo->_id,
                    'author' => '',
                    'msg_index' => $wz_item['itemidx'],
                    'copyright_stat' => 100,
                    'qunfa_id' => 0,
                    'type' => 49,
                    'like_count' => 0,
                    'read_count' => 0,
                    'comment_count' => 0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$uuid,$article->_id);
                (new GetArticleBody($article->_id))->handle();
                if ($mpInfo->is_auto_publish == 1 && $article->date_time >= date('Y-m-d 00:00:00',strtotime('-1 days'))) {
                    dispatch(new ArticleToSubmission($article->_id));
                }
            }
            $mpInfo->update_time = date('Y-m-d H:i:s');
            $mpInfo->save();
            sleep(rand(10,20));
        }
        var_dump($successCount);
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
            if ($count > 0) {
                TaskLogic::alertManagerPendingArticles($count);
            }
        }
    }

}