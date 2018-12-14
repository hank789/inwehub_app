<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Logic\TagsLogic;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\RateLimiter;
use Illuminate\Console\Command;

class TagProductDict extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:dict:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化产品的词典';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->leftJoin('tags','tag_id','=','tags.id');
        $fields = ['tag_category_rel.id','tag_category_rel.tag_id','tag_category_rel.category_id','tag_category_rel.status','tag_category_rel.reviews','tags.name','tags.logo','tags.summary','tags.created_at'];
        $page = 1;
        $perPage = 1000;
        $tags = $query->select($fields)->simplePaginate($perPage,['*'],'page',$page);
        while ($tags->count() > 0) {
            foreach ($tags as $tag) {
                TagsLogic::cacheProductTags($tag);
            }
            $page ++;
            $tags = $query->select($fields)->simplePaginate($perPage,['*'],'page',$page);
        }

    }

}