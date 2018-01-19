<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Jobs\FixUserCredits;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Console\Command;

class FixTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复tag数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userTags = UserTag::where('industries','>',0)->get();
        foreach ($userTags as $userTag) {
            $tag = Tag::find($userTag->tag_id);
            if ($tag->category_id != 9) {
                continue;
            }
            $newTags = Tag::where('name',$tag->name)->whereIn('category_id',[23,29])->get();
            foreach ($newTags as $newTag) {
                $userTag->tag_id = $newTag->id;
                $userTag->save();
            }
        }
        $taggables = Taggable::get();
        foreach ($taggables as $taggable) {
            $tag = Tag::find($taggable->tag_id);
            if ($tag->category_id != 9) {
                continue;
            }
            $newTags = Tag::where('name',$tag->name)->whereIn('category_id',[23,29])->get();
            foreach ($newTags as $newTag) {
                $taggable->tag_id = $newTag->id;
                $taggable->save();
            }
        }
        Tag::where('category_id',9)->delete();
        Category::where('id',9)->delete();
    }

}