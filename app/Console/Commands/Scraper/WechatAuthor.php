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


class WechatAuthor extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:wechat:author';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取微信公众号';
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
        $path = env('SPIDER_PATH');
        if($path){
            shell_exec('cd '.$path.' && python auto_add_mp.py >> /tmp/auto_add_mp.log');
            $this->call('scraper:wechat:posts');
        }
    }
}