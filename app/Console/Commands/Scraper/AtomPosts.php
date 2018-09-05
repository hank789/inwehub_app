<?php namespace App\Console\Commands\Scraper;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:36
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\SystemNotify;
use App\Jobs\ArticleToSubmission;
use App\Logic\TaskLogic;
use App\Models\Scraper\Feeds;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Third\RssFeed;
use Carbon\Carbon;
use DateTime;
use PHPHtmlParser\Dom;

use Illuminate\Console\Command;

class AtomPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:atom {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atom文章抓取';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $id = $this->argument('id');
        $query = Feeds::query();
        if($id){
            $query->where('id',$id);
        }
        $lists = $query->orderBy('id', 'desc')
            ->where('source_type', 2)
            ->where('status',1)
            ->get();
        if($lists->count()<=0) return;

        foreach ($lists as $key => $topic) {
            $source_link = $topic->source_link;
            $this->info($source_link);
            try {
                $xml = RssFeed::loadAtom($source_link);
            } catch (\Exception $e) {
                app('sentry')->captureException($e);
                event(new SystemNotify('RSS抓取失败：'.$topic->source_link));
                continue;
            }

            foreach ($xml->entry as $key => $value) {
                $image_url   = '';
                $author_name = '';
                $author_link = '';

                $dom = new Dom();
                $dom->load($value->content);

                $img_tags = $dom->find('img');
                foreach ($img_tags as $img) {
                    $image_url = $img->getAttribute('src');
                    // 如果图片链接为空，则跳过下方的处理
                    if (strlen($image_url) === 0) {
                        continue;
                    }
                    // 省略嵌入式资源处理
                    if (0 === strpos($image_url, '//')) {
                        $image_url = 'http:' . $image_url;
                    }
                    // 判断是否为 .jpg、.jpeg、.png、.bmp 类型的图片，如果不是则跳过
                    if (strpos($image_url, '.jpg')  > 0 ||
                        strpos($image_url, '.jpeg') > 0 ||
                        strpos($image_url, '.png')  > 0 ||
                        strpos($image_url, '.bmp')  > 0) {
                        break;
                    } else {
                        $image_url = '';
                    }
                }

                if ($value->author) {
                    $author_name = $value->author->name;
                    $author_link = $value->author->uri;
                }

                $published_at = new DateTime();
                if (strlen((string)$value->published) > 0) {
                    $published_at = new DateTime($value->published);
                } elseif (strlen((string)$value->updated) > 0) {
                    $published_at = new DateTime($value->updated);
                }

                if (empty($image_url)) {
                    $image_url = getUrlImg($value->link->attributes()->href);
                }

                $keywords = $topic->keywords;
                $status = 3;//默认已删除
                if ($keywords) {
                    $content = $value->title.';'.$value->summary;
                    $keywordsArr = explode('|',$keywords);
                    foreach ($keywordsArr as $keyword) {
                        if (strchr($content,$keyword)) {
                            $status = 1;
                            break;
                        }
                    }
                } else {
                    $status = 1;
                }

                WechatWenzhangInfo::firstOrCreate(['content_url' => $value->link->attributes()->href],[
                    'content_url'           => $value->link->attributes()->href,
                    'title'          => $value->title,
                    'author'    => $author_name,
                    'site_name'      => $topic->name,
                    'topic_id'       => 0,
                    'mp_id'          => $topic->id,
                    'mobile_url'    => '',
                    'date_time'   => $published_at,
                    'source_type' => 2,
                    'description' => $value->summary,
                    'cover_url'   => $image_url,
                    'status'         => $status
                ]);
            }
        }
        $articles = WechatWenzhangInfo::where('source_type',2)->where('topic_id',0)->where('status',1)->where('date_time','>=',date('Y-m-d 00:00:00',strtotime('-1 days')))->get();
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
