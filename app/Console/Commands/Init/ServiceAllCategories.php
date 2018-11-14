<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Category;
use App\Services\RateLimiter;
use App\Services\Translate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use QL\Ext\PhantomJs;
use QL\QueryList;

class ServiceAllCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:service:categories:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化点评产品所有分类';

    protected $ql;

    protected $mapping;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ql = QueryList::getInstance();
        $this->ql->use(PhantomJs::class,config('services.phantomjs.path'));
        $this->mapping = [
            'CRM & Related' => 'crm-related',
            'E-Commerce' => 'e-commerce',
            'Security' => 'security',
            'ERP' => 'erp',
            'Supply Chain & Logistics' => 'supply-chain-logistics',
            'Vertical Industry' => 'vertical-industry',
            'Hosting' => 'hosting',
            'HR' => 'hr',
            'Analytics' => 'analytics',
            'Artificial Intelligence' => 'artificial-intelligence',
            'IT Infrastructure' => 'it-infrastructure',
            'B2B Marketplace Platforms' => 'b2b-marketplace-platforms',
            'CAD & PLM' => 'cad-plm',
            'Collaboration & Productivity' => 'collaboration-productivity',
            'Content Management' => 'content-management',
            'IT Management' => 'it-management',
            'Development' => 'development',
            'Office' => 'office',
            'Digital Advertising' => 'digital-advertising',

            'Business Services' => 'business-services',
            'Professional Services' => 'professional-services-bd3033f2-37d6-456f-88bb-632ef5fc83f5',
            'Staffing Services' => 'staffing-services',
            'Translation Services' => 'translation-services',
            'Cybersecurity Services' => 'cybersecurity-services',
            'Value-Added Resellers (VARs)' => 'value-added-resellers-vars',
            'Marketing Services' => 'marketing-services',
            'Other Services' => 'other-services-a3556bb7-df48-4d51-af00-82ad0505f4c5'
        ];

        //抓取g2所有产品分类
        $url = 'https://www.g2crowd.com/categories?category_type=software';
        $html = $this->ql->browser($url);
        $data = $html->rules([
            'name' => ['h4.color-secondary','text'],
            'list' => ['h4~div.ml-2','html']
        ])->range('div.newspaper-columns__list-item.pb-1')->query()->getData(function ($item) {
            if (!isset($item['list'])) return $item;
            $item['list'] = $this->ql->html($item['list'])->rules([
                'name' => ['a.text-medium','text'],
                'link' => ['a.text-medium','href']
            ])->range('')->query()->getData();
            return $item;
        });

        $softwarePrefix = 'enterprise_product_';
        $servicePrefix = 'enterprise_service_';
        $this->info('抓取产品分类');
        foreach ($data as $item) {
            $this->addC($item,$softwarePrefix);
        }

        $html = $this->ql->browser('https://www.g2crowd.com/categories?category_type=service');
        $data = $html->rules([
            'name' => ['h4.color-secondary','text'],
            'list' => ['h4~div.ml-2','html']
        ])->range('div.newspaper-columns__list-item.pb-1')->query()->getData(function ($item) {
            if (!isset($item['list'])) return $item;
            $item['list'] = $this->ql->html($item['list'])->rules([
                'name' => ['a.text-medium','text'],
                'link' => ['a.text-medium','href']
            ])->range('')->query()->getData();
            return $item;
        });
        $this->info('抓取服务分类');
        foreach ($data as $item) {
            $this->addC($item,$servicePrefix);
        }

        $categoriesList = Category::where('type','enterprise_review')->where('parent_id','>',0)->get();
        foreach ($categoriesList as $categoryItem) {
            if (RateLimiter::instance()->hGet('g2_category_finished',$categoryItem->slug)) {
                continue;
            }
            $slug = str_replace('enterprise_product_','',$categoryItem->slug);
            $slug = str_replace('enterprise_service_','',$slug);
            if (str_contains($categoryItem->slug,'enterprise_product_')) {
                $prefix = 'enterprise_product_';
            } elseif (str_contains($categoryItem->slug,'enterprise_service_')) {
                $prefix = 'enterprise_service_';
            } else {
                $this->error($categoryItem->slug);
                $prefix = 'enterprise_product_';
            }
            $url = 'https://www.g2crowd.com/categories/'.$slug;
            $html = $this->ql->browser($url);
            $data = $html->rules([
                'name' => ['a','text'],
                'link' => ['a','href']
            ])->range('div.paper.mb-2>div')->query()->getData();
            if ($data->count() > 0) {
                foreach ($data as $item) {
                    $name = formatHtml($item['name']);
                    $slug2 = str_replace('/categories/','',$item['link']);
                    $children = Category::where('slug',$prefix.$slug2)->first();
                    if (!$children) {
                        $this->info($item['name']);
                        $children = Category::create([
                            'parent_id' => $categoryItem->id,
                            'grade'     => 1,
                            'name'      => config('app.env') == 'production'?Translate::instance()->translate($name):$name,
                            'icon'      => $name,
                            'slug'      => $prefix.$slug2,
                            'type'      => 'enterprise_review',
                            'sort'      => 0,
                            'status'    => 1
                        ]);
                    }
                    $this->addChildren($item,$children,$prefix);
                }
            }
            RateLimiter::instance()->hSet('g2_category_finished',$categoryItem->slug,1);
        }


        $categoriesAll = Category::where('type','enterprise_review')->get();
        foreach ($categoriesAll as $category) {
            $children = Category::where('parent_id',$category->id)->first();
            if (!$children) {
                $category->grade = 0;
            } else {
                $category->grade = 1;
            }
            $category->save();
        }
        $this->info($categoriesAll->count());
    }

    protected function addC($item, $softwarePrefix) {
        $name = formatHtml($item['name']);
        if (isset($this->mapping[$name])) {
            //已经录入
            $slug = $this->mapping[$name];
        } else {
            $slug = str_replace(' ','-',strtolower($name));
            $slug = str_replace('&','-',$slug);
            $slug = str_replace('/','-',$slug);
        }
        $category = Category::where('slug',$softwarePrefix.$slug)->first();
        if (!$category) {
            $this->info($name);
            $category = Category::create([
                'parent_id' => $softwarePrefix=='enterprise_product_'?43:44,
                'grade'     => 1,
                'name'      => config('app.env') == 'production'?Translate::instance()->translate($name):$name,
                'icon'      => $name,
                'slug'      => $softwarePrefix.$slug,
                'type'      => 'enterprise_review',
                'sort'      => 0,
                'status'    => 1
            ]);
        }
        foreach ($item['list'] as $item2) {
            $slug2 = str_replace('/categories/','',$item2['link']);
            $children = Category::where('slug',$softwarePrefix.$slug2)->first();
            if (!$children) {
                $name2 = formatHtml($item2['name']);
                $this->info($name2);
                $children = Category::create([
                    'parent_id' => $category->id,
                    'grade'     => 1,
                    'name'      => config('app.env') == 'production'?Translate::instance()->translate($name2):$name2,
                    'icon'      => $name2,
                    'slug'      => $softwarePrefix.$slug2,
                    'type'      => 'enterprise_review',
                    'sort'      => 0,
                    'status'    => 1
                ]);
            }
        }
    }

    protected function addChildren($v,$categoryItem,$prefix) {
        $data = $this->ql->browser('https://www.g2crowd.com'.$v['link'])->rules([
            'name' => ['a','text'],
            'link' => ['a','href']
        ])->range('div.paper.mb-2>div')->query()->getData();
        if ($data->count() > 0) {
            foreach ($data as $item) {
                $name = formatHtml($item['name']);
                $slug2 = str_replace('/categories/','',$item['link']);
                $children = Category::where('slug',$prefix.$slug2)->first();
                if (!$children) {
                    $this->info($item['name']);
                    $children = Category::create([
                        'parent_id' => $categoryItem->id,
                        'grade'     => 1,
                        'name'      => config('app.env') == 'production'?Translate::instance()->translate($name):$name,
                        'icon'      => $name,
                        'slug'      => $prefix.$slug2,
                        'type'      => 'enterprise_review',
                        'sort'      => 0,
                        'status'    => 1
                    ]);
                }
                $this->addChildren($item,$children,$prefix);
            }
        }
    }
}