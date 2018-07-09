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
        Tag::updateOrCreate([
            'name'          => '新业态',
        ],[
            'name'          => '新业态',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '供应链',
        ],[
            'name'          => '供应链',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '企业战略',
        ],[
            'name'          => '企业战略',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '数字经济新价值',
        ],[
            'name'          => '数字经济新价值',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => 'B2B与平台化',
        ],[
            'name'          => 'B2B与平台化',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '数字化企业',
        ],[
            'name'          => '数字化企业',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '智能制造与工业4.0',
        ],[
            'name'          => '智能制造与工业4.0',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '大数据与AI',
        ],[
            'name'          => '大数据与AI',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '企业服务',
        ],[
            'name'          => '企业服务',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '咨询行业',
        ],[
            'name'          => '咨询行业',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => 'SAP与Oralce',
        ],[
            'name'          => 'SAP与Oralce',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => 'SaaS与云',
        ],[
            'name'          => 'SaaS与云',
            'category_id'   => $category->id
        ]);
        Tag::updateOrCreate([
            'name'          => '信息化新技术',
        ],[
            'name'          => '信息化新技术',
            'category_id'   => $category->id
        ]);
        $tags = Tag::all();
        foreach ($tags as $tag) {
            TagCategoryRel::updateOrCreate([
                'tag_id' => $tag->id
            ],[
                'tag_id' => $tag->id,
                'category_id' => $tag->category_id
            ]);
        }
    }

}