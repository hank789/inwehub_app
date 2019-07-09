<?php

namespace App\Console\Commands\Scraper;

use App\Events\Frontend\System\ExceptionNotify;
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

class RssPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:rss {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RSS文章抓取';

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
            ->where('source_type', 1)
            ->where('status',1)
            ->get();

        if($lists->count()<=0) return;
        foreach ($lists as $key => $topic) {
            $source_link = $topic->source_link;
            $this->info($source_link);
            try {
                $xml = RssFeed::loadRss($source_link);
            } catch (\Exception $e) {
                app('sentry')->captureException($e);
                event(new ExceptionNotify('RSS抓取失败：'.$topic->source_link));
                continue;
            }

            foreach ($xml->channel->item as $key => $value) {
                $image_url    = '';
                $author       = '';
                $article_tags = [];
                $this->info($value->title);
                if (strtotime($value->pubDate) <= strtotime('-7 days')) {
                    continue;
                }

                $author = $value->author;
                if (strlen($author) === 0) {
                    $author = $value->children('dc', true)->creator;
                }
                try {
                    $dom = new Dom();
                    $dom->load($value->description);
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
                } catch (\Exception $e) {
                    $this->warn($e->getMessage());
                }

                // 获取文章的标签
                /*$category_tags = $value->category;
                foreach ($category_tags as $category) {
                    array_push($article_tags, new ArticleTag(['name' => ((string) $category)]));
                    // 创建标签，如果存在则会被忽略掉
                    Tag::firstOrCreate(['name' => ((string) $category)]);
                }*/

                $guid = $value->guid;
                if (!$guid) {
                    $guid = $value->link;
                }
                if (empty($image_url)) {
                    $info = getUrlInfo((string)$value->link,true, 'submissions', false);
                    $image_url = $info['img_url'];
                }
                $keywords = $topic->keywords;
                $description = formatHtml($value->description);
                $title = formatHtml($value->title);
                $status = 3;//默认已删除
                if ($keywords) {
                    $content = $title.';'.$description;
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

                $article = WechatWenzhangInfo::firstOrCreate(['content_url' => $value->link],[
                    'content_url'           => $value->link,
                    'title'          => $title,
                    'author'    => $author,
                    'site_name'      => $topic->name,
                    'topic_id'       => 0,
                    'mp_id'          => $topic->id,
                    'mobile_url'     => '',
                    'date_time'   => new DateTime($value->pubDate),
                    'source_type' => 2,
                    'description' => $description,
                    'cover_url'   => $image_url,
                    'status'         => $status
                ]);
                if ($status == 1) {
                    dispatch(new ArticleToSubmission($article->_id));
                }

                /*if ($topic->is_auto_publish == 1 && $status == 1) {
                    dispatch(new ArticleToSubmission($article->_id));
                }*/
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
