<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */
use App\Models\Category;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use Illuminate\Console\Command;

class FixAutoTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:auto:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复自动标签';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tags = Tag::where('category_id',1)->get();
        foreach ($tags as $tag) {
            Taggable::where('tag_id',$tag->id)->update(['is_display'=>0]);
        }
    }

}