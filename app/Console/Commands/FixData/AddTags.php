<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */
use App\Models\Category;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use Illuminate\Console\Command;

class AddTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:add:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '增加标签';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $category = Category::firstOrCreate(['slug'      => 'region'],[
            'parent_id' => 0,
            'grade'     => 1,
            'name'      => '领域',
            'slug'      => 'region',
            'type'      => 'tags,articles',
            'sort'      => 0,
            'status'    => 1
        ]);
        Tag::firstOrCreate([
            'name'          => '新业态',
            'category_id'   => $category->id
        ],[
            'name'          => '新业态',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '供应链',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '企业战略',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '数字经济新价值',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => 'B2B与平台化',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '数字化企业',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '智能制造与工业4.0',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '大数据与AI',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '企业服务',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '咨询行业',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => 'SAP与Oralce',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => 'SaaS与云',
            'category_id'   => $category->id
        ]);
        Tag::firstOrCreate([
            'name'          => '信息化新技术',
            'category_id'   => $category->id
        ]);
        $tags = Tag::all();
        foreach ($tags as $tag) {
            TagCategoryRel::firstOrCreate([
                'tag_id' => $tag->id,
                'category_id' => $tag->category_id
            ]);
        }
    }

}