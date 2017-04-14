<?php namespace App\Console\Commands\Scraper;
/**
 * @author: wanghui
 * @date: 2017/4/13 下午8:36
 * @email: wanghui@yonglibao.com
 */
use App\Models\Inwehub\Feeds;
use App\Models\Inwehub\News;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use Carbon\Carbon;
use PHPHtmlParser\Dom;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

class AtomPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:atom';

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
        $lists = Feeds::orderBy('id', 'desc')
            ->where('source_type', 2)
            ->get();
        if($lists->count()<=0) return;

        $client = new Client();

        $requests = function ($lists) {
            foreach ($lists as $key => $topic) {
                $source_link = $topic->source_link;
                $this->info($source_link);
                yield new Request('GET', $source_link);
            }
        };

        $pool = new Pool($client, $requests($lists), [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) use ($lists) {
                // this is delivered each successful response
                $body = mb_convert_encoding($response->getBody()->getContents(), "UTF-8");

                $xml = simplexml_load_string( $body );

                $topic = $lists[$index];

                $data = [
                    'user_id'  => $topic->user_id,
                    'topic_id' => $topic->id,
                ];

                foreach ($xml->entry as $key => $value) {
                    $image_url   = '';
                    $author_name = '';
                    $author_link = '';

                    $dom = new Dom();
                    $dom->load($value->content);

                    /*$img_tags = $dom->find('img');
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
                    }*/

                    if ($value->author) {
                        $author_name = $value->author->name;
                        $author_link = $value->author->uri;
                    }

                    $article = News::firstOrCreate(array_merge($data, ['url' => $value->id]));

                    $published_at = new DateTime();
                    if (strlen((string)$value->published) > 0) {
                        $published_at = new DateTime($value->published);
                    } elseif (strlen((string)$value->updated) > 0) {
                        $published_at = new DateTime($value->updated);
                    }

                    $article->update([
                        'url'           => $value->link->attributes()->href,
                        'mobile_url'    => $value->link->attributes()->href,
                        'title'          => $value->title,
                        'site_name'      => $topic->name,
                        'author_name'    => $author_name,
                        'publish_date'   => $published_at,
                        'topic_id'       => 0,
                        'user_id'        => 1,
                        'status'         => 1
                    ]);
                }
            },
            'rejected' => function ($reason, $index) {
                // this is delivered each failed request
                $this->error("rejected reason: " . $reason );
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
    }
}
