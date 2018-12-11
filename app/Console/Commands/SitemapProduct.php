<?php namespace App\Console\Commands;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Answer;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\TagCategoryRel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SitemapProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate:product {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成产品的sitemap文件';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sitemap = \App::make("sitemap");
        $count = 0;
        $date = $this->argument('date');
        //$date = '2018-12-03 00:00:00';
        if (!$date) {
            $date = date('Y-m-d H:i:s',strtotime('-24 hours'));
        }
        $this->info($date);
        $urls = [];

        //点评产品详情
        $page = 1;
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->orderBy('tag_id','desc');
        $tags = $query->simplePaginate(500,['*'],'page',$page);
        while ($tags->count() > 0) {
            foreach ($tags as $tag) {
                if (isset($urls[$tag->tag_id])) continue;
                if (strtotime($tag->tag->created_at) >= strtotime($date)) {
                    $count++;
                    $url = 'https://www.inwehub.com/dianping/product/'.rawurlencode($tag->tag->name);
                    $sitemap->add($url, (new Carbon($tag->tag->created_at))->toAtomString(), '1.0', 'monthly');
                    $urls[$tag->tag_id] = $url;
                }
            }
            $page ++;
            $tags = $query->simplePaginate(500,['*'],'page',$page);
        }

        $sitemap->store('xml', 'sitemap_product');
        $this->info('共生成地址：'.$count);
        $newUrls = array_chunk($urls,2000);
        foreach ($newUrls as $newUrl) {
            $result = submitUrlsToSpider($newUrl);
            var_dump($result);
        }
    }

}