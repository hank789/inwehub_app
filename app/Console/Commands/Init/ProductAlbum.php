<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Category;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use Illuminate\Console\Command;

class ProductAlbum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:product-album';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化产品专辑';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $product_c = Category::create([
            'parent_id' => 0,
            'grade'     => 1,
            'name'      => '产品专辑',
            'icon'      => null,
            'slug'      => 'product_album',
            'type'      => 'product_album',
            'sort'      => 0,
            'status'    => 1
        ]);
        $categories = [
            [
                'name' => '分析与商业智能',
                'slug' => 'product_album_business_intelligence_analytics',
                'type' => 'product_album'
            ],
            [
                'name' => 'CRM',
                'slug' => 'product_album_crm',
                'type' => 'product_album'
            ],
            [
                'name' => 'OA与协同',
                'slug' => 'product_album_oa',
                'type' => 'product_album'
            ],
            [
                'name' => '基础架构服务（laaS）',
                'slug' => 'product_album_laas',
                'type' => 'product_album'
            ],
            [
                'name' => 'PLM',
                'slug' => 'product_album_plm',
                'type' => 'product_album'
            ],
            [
                'name' => 'ERP软件',
                'slug' => 'product_album_erp',
                'type' => 'product_album'
            ],
            [
                'name' => 'HRMS',
                'slug' => 'product_album_hrms',
                'type' => 'product_album'
            ],
            [
                'name' => '表单调查',
                'slug' => 'product_album_form',
                'type' => 'product_album'
            ],
            [
                'name' => '舆情监控工具',
                'slug' => 'product_album_public_sentiment_monitoring',
                'type' => 'product_album'
            ]
        ];
        foreach ($categories as $category) {
            Category::create([
                'parent_id' => $product_c->id,
                'grade'     => 0,
                'name'      => $category['name'],
                'icon'      => 'https://cdn.inwehub.com/system/group_18@3x.png',
                'slug'      => $category['slug'],
                'type'      => $category['type'],
                'sort'      => 0,
                'status'    => 1
            ]);
        }
    }

}