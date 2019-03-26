<?php namespace App\Console\Commands\Scraper;
use App\Jobs\ArticleToSubmission;
use App\Jobs\GetArticleBody;
use App\Logic\TaskLogic;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use App\Services\WechatGzhService;
use Carbon\Carbon;
use function GuzzleHttp\Psr7\parse_query;
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
        $flag = true;
        var_dump(date('Y-m-d H:i:s').'开始抓取');
        foreach ($mpInfos as $mpInfo) {
            $this->info($mpInfo->name);
            //一个小时内刚处理过的跳过
            if (strtotime($mpInfo->update_time) >= strtotime('-120 minutes')) continue;
            $wz_list = false;
            //先通过公众号服务取50次数据
            if ($successCount < 15 && $flag) {
                $wz_list = $spider->getGzhArticles($mpInfo);
            }
            if ($wz_list === false || $successCount >= 15) {
                $flag = false;
                //只执行上面的服务50次
                if ($mpInfo->qr_url) {
                    $wz_list = WechatGzhService::instance()->getProfile($mpInfo->qr_url);
                    if ($wz_list) {
                        $newItems = [];
                        foreach ($wz_list as $key=>&$item) {
                            if (!isset($item['app_msg_ext_info'])) {
                                unset($wz_list[$key]);
                                continue;
                            }
                            $item['title'] = $item['app_msg_ext_info']['title'];
                            $item['digest'] = $item['app_msg_ext_info']['digest'];
                            $item['link'] = formatHtml($item['app_msg_ext_info']['content_url']);
                            $item['update_time'] = $item['comm_msg_info']['datetime'];
                            $item['aid'] = $item['comm_msg_info']['id'];
                            $item['cover'] = $item['app_msg_ext_info']['cover'];
                            $item['author'] = $item['app_msg_ext_info']['author'];
                            $item['itemidx'] = $item['app_msg_ext_info']['fileid'];
                            $item['copyright_stat'] = $item['app_msg_ext_info']['copyright_stat']??100;
                            $item['type'] = $item['comm_msg_info']['type'];
                            if (count($item['app_msg_ext_info']['multi_app_msg_item_list']) >= 1) {
                                foreach ($item['app_msg_ext_info']['multi_app_msg_item_list'] as $multi_item) {
                                    $newItems[] = [
                                        'title' => $multi_item['title'],
                                        'digest' => $multi_item['digest'],
                                        'link' => $multi_item['content_url'],
                                        'update_time' => $item['update_time'],
                                        'aid' => $multi_item['fileid'],
                                        'cover' => $multi_item['cover'],
                                        'author' => $multi_item['author'],
                                        'itemidx' => $multi_item['fileid'],
                                        'copyright_stat' => $multi_item['copyright_stat']??100,
                                        'type' => $item['comm_msg_info']['type']
                                    ];
                                }
                            }
                        }
                        if ($newItems) {
                            $wz_list = array_merge($wz_list,$newItems);
                        }
                    }
                }
            } else {
                $successCount++;
            }

            if ($wz_list === false) {
                break;
            }
            foreach ($wz_list as $wz_item) {
                $this->info($wz_item['title']);
                if ($wz_item['update_time'] <= strtotime('-2 days')) continue;
                $wz_item['title'] = formatHtml($wz_item['title']);
                $wz_item['digest'] = formatHtml($wz_item['digest']);

                $uuid = base64_encode($mpInfo->_id.$wz_item['title'].date('Y-m-d',$wz_item['update_time']));
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
                    'author' => $wz_item['author']??'',
                    'msg_index' => $wz_item['itemidx'],
                    'copyright_stat' => $wz_item['copyright_stat']??100,
                    'qunfa_id' => 0,
                    'type' => $wz_item['type']??49,
                    'like_count' => 0,
                    'read_count' => 0,
                    'comment_count' => 0
                ]);
                if (empty($mpInfo->qr_url)) {
                    $parse_url = parse_url($content_url);
                    $query = parse_query($parse_url['query']);
                    $mpInfo->qr_url = $query['__biz'];
                }
                $article->addProductTag();

                RateLimiter::instance()->hSet('wechat_article',$uuid,$article->_id);
                (new GetArticleBody($article->_id))->handle();
                if ($mpInfo->is_auto_publish == 1 && $article->date_time >= date('Y-m-d 00:00:00',strtotime('-1 days'))) {
                    dispatch(new ArticleToSubmission($article->_id));
                }
            }
            $mpInfo->update_time = date('Y-m-d H:i:s');
            $mpInfo->save();
            if ($successCount < 50) {
                sleep(rand(10,20));
            } else {
                sleep(rand(3,8));
            }
        }
        Artisan::call('scraper:wechat:posts');
        var_dump(date('Y-m-d H:i:s').'抓取完成：'.$successCount);
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