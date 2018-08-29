<?php namespace App\Console\Commands\Scraper;
use App\Events\Frontend\System\SystemNotify;
use App\Jobs\ArticleToSubmission;
use App\Jobs\GetArticleBody;
use App\Models\Scraper\WechatWenzhangInfo;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
        $path = config('app.spider_path');
        if($path){
            shell_exec('cd '.$path.' && python updatemp.py >> /tmp/updatemp.log');
            $articles = WechatWenzhangInfo::where('source_type',1)->where('topic_id',0)->where('status',1)->where('date_time','>=',date('Y-m-d 00:00:00',strtotime('-1 days')))->get();
            foreach ($articles as $article) {
                dispatch(new GetArticleBody($article->_id));
            }
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
                    event(new SystemNotify('新抓取'.$count.'篇文章，请及时去后台处理',[]));
                }
            }
        }
    }

}