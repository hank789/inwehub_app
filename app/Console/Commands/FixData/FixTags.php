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
            if (!$tag) {
                $userTag->delete();
                continue;
            }
            $newTag = Tag::where('name',$tag->name)->where('id','!=',$tag->id)->first();
            if ($newTag && $newTag->category_id == 23) {
                $userTag->tag_id = $newTag->id;
                $userTag->save();
            } elseif($tag->category_id != 23) {
                $this->comment($tag->name);
                $userTag->delete();
            }
        }
        Tag::where('category_id',9)->delete();
        Category::where('id',9)->delete();
    }

}