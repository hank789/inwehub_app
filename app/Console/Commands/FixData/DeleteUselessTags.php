<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Models\Attention;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\UserTag;
use Illuminate\Console\Command;

class DeleteUselessTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:delete_useless:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除无用的tag数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tags = Tag::where('name','其他')->get();
        foreach ($tags as $tag) {
            /*删除关注*/
            Attention::where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->delete();
            $tag->userTags()->delete();
            /*删除用户标签*/
            UserTag::where('tag_id','=',$tag->id)->delete();
            Taggable::where('tag_id',$tag->id)->delete();
            $tag->delete();
        }
    }

}