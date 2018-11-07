<?php
/**
 * @author: wanghui
 * @date: 2018/11/6 下午6:14
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Console\Commands\Scraper;

use App\Models\Category;
use App\Models\Tag;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use QL\Ext\PhantomJs;
use QL\QueryList;

class Capterra extends Command
{

    protected $signature = 'init:scraper:capterra';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取网站capterra的数据';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $cUrl = 'https://www.capterra.com/categories';
        $baseU = 'https://www.capterra.com';

        $content = $this->ql->browser($cUrl)->rules([
            'name' => ['a','text'],
            'link' => ['a','href']
        ])->range('ol.nav.browse-group-list>li')->query()->getData();
        foreach ($content as $item) {
            $category = Category::where('slug',$item['link'])->first();
            if (!$category) {
                $category = Category::create([
                    'parent_id' => 0,
                    'grade'     => 0,
                    'name'      => $item['name'],
                    'icon'      => null,
                    'slug'      => $item['link'],
                    'type'      => 'capterra_review',
                    'sort'      => 0,
                    'status'    => 1
                ]);
            }
            $data = $this->ql->browser($baseU.'/'.$item['link'])->rules([
                'name' => ['h2.listing-name>a','text'],
                'total' => ['a.reviews-count.milli','text']
            ])->range('div.card.listing')->query()->getData();
            foreach ($data as $item2) {
                $this->info($item2['name']);
                $reviews = trim($item2['total'],'()');
                $reviews = trim(str_replace('reviews','',$reviews));
                $tag = Tag::where('name',$item2['name'])->first();
                if (!$tag) {
                    $tag = Tag::create([
                        'name' => $item2['name'],
                        'category_id' => $category->id,
                        'logo' => '',
                        'summary' => '',
                        'description' => '',
                        'parent_id' => 0,
                        'reviews' => $reviews
                    ]);
                }
            }
        }
    }
}