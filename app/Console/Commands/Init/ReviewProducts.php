<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Category;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\Translate;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use QL\Ext\PhantomJs;
use QL\QueryList;

class ReviewProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:service:review-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化点评产品';

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
        $categories = Category::where('type','enterprise_review')->where('grade',0)->get();
        foreach ($categories as $category) {
            $slug = str_replace('enterprise_product_','',$category->slug);
            $slug = str_replace('enterprise_service_','',$slug);
            $page=1;
            $needBreak = false;
            while (true) {
                $data = $this->rules1($slug,$page);
                if ($data->count() <= 0) {
                    sleep(5);
                    $data = $this->rules1($slug,$page);
                }
                if ($data->count() <= 0 && $page==1) {
                    $data = $this->rules2($slug);
                    if ($data->count() <= 0) {
                        $data = $this->rules2($slug);
                    }
                    $needBreak = true;
                }
                if ($data->count()) {
                    foreach ($data as $item) {
                        $logo = $item['logo']?:$item['logo1'];
                        $this->info($logo);
                        $tag = Tag::where('name',$item['name'])->first();
                        $item['total'] = str_replace(',','',trim($item['total'],'()'));
                        if(!$tag) {
                            $description = $item['description'];
                            if (config('app.env') == 'production') {
                                $description = Translate::instance()->translate($item['description']);
                            }
                            $tag = Tag::create([
                                'name' => $item['name'],
                                'category_id' => $category->id,
                                'logo' => saveImgToCdn($logo,'tags'),
                                'summary' => $description,
                                'description' => $item['description'],
                                'parent_id' => 0,
                                'reviews' => $item['total']
                            ]);
                        }
                        RateLimiter::instance()->hSet('review-tags-url',$tag->id,$item['link']);
                        $tagRel = TagCategoryRel::where('tag_id',$tag->id)->where('category_id',$category->id)->first();
                        if (!$tagRel) {
                            TagCategoryRel::create([
                                'tag_id' => $tag->id,
                                'category_id' => $category->id,
                                'review_average_rate' => $item['rate'],
                                'review_rate_sum' => floatval($item['total'])*floatval($item['rate']),
                                'reviews' => $item['total'],
                                'type' => TagCategoryRel::TYPE_REVIEW
                            ]);
                        }
                    }
                } else {
                    $this->info('无数据:'.$slug.';page:'.$page);
                    break;
                }
                if ($needBreak) break;
                $this->info('page:'.$page);
                if ($page>=200) break;
                //if (config('app.env') != 'production' && $page >= 2) break;
                $page++;
            }
        }
    }

    protected function rules1($slug,$page) {
        $url = 'https://www.g2crowd.com/categories/'.$slug.'?order=g2_score&page='.$page.'#product-list';
        $html = $this->ql->browser($url)->rules([
            'name' => ['h5','text'],
            'link' => ['a','href'],
            'logo' => ['img','data-deferred-image-src'],
            'logo1' => ['img','src'],
            'description' => ['p','text'],
            'rate' => ['div.mr-4th','text'],
            'total' => ['div.as-fe','text']
        ])->range('div#product-list>div.mb-2')->query()->getData();
        //var_dump($this->ql->browser($url)->getHtml());
        return $html;
    }

    protected function rules2($slug) {
        $url = 'https://www.g2crowd.com/categories/'.$slug;
        $html = $this->ql->browser($url)->rules([
            'name' => ['div.ellipsis--2-lines','text'],
            'link' => ['a','href'],
            'logo' => ['img','src'],
            'description' => ['p.inwehub','text'],
            'rate' => ['div.mr-4th','text'],
            'total' => ['div.as-fe','text']
        ])->range('div.row.small-up-1.medium-up-2.large-up-3>div.column')->query()->getData();
        //var_dump($this->ql->browser($url)->getHtml());
        return $html;
    }

}