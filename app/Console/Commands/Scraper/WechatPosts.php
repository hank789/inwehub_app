<?php namespace App\Console\Commands\Scraper;
use App\Models\Inwehub\Feeds;
use App\Models\Inwehub\News;
use App\Services\WechatPostSpider;
use Illuminate\Console\Command;
use Goutte\Client;

/**
 * @author: wanghui
 * @date: 2017/4/13 下午7:42
 * @email: wanghui@yonglibao.com
 */


class WechatPosts extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:posts';
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
        $client = new Client();
        $feeds = Feeds::where('source_format','wechat')->get();
        foreach (config('post-urls') as $url) {
            /**
             * 这里 url 可能需要索引，但是用 url 做唯一标示不太好，索引太大
             */
            if (News::where('url', $url)->exists()) {
                continue;
            }
            $wechatPostSpider = new WechatPostSpider($client, $url);
            $this->savePost($wechatPostSpider);
            $this->info('create one post!');
        }
    }
    protected function savePost(WechatPostSpider $wechatPostSpider)
    {
        Post::create([
            'url' => $wechatPostSpider->getUrl(),
            'author' => $wechatPostSpider->getAuthor(),
            'title' => $wechatPostSpider->getTitle(),
            'content' => $wechatPostSpider->getContent(),
            'post_date' => $wechatPostSpider->getPostDate(),
        ]);
    }
}