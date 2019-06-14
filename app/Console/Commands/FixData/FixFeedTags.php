<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */


use App\Models\Comment;
use App\Models\Feed\Feed;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Console\Command;

class FixFeedTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:feed_tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复feed的标签';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $feeds = Feed::get();
        foreach ($feeds as $feed) {
            $tags = [];
            switch ($feed->source_type) {
                case 'App\Models\Question':
                    $source = $feed->source;
                    $tags = $source->tags()->pluck('tag_id')->toArray();
                    break;
                case 'App\Models\Answer':
                    $source = $feed->source;
                    if (!$source) {
                        $feed->delete();
                        break;
                    }
                    $tags = $source->question->tags()->pluck('tag_id')->toArray();
                    break;
                case 'App\Models\Submission':
                case 'App\Models\Readhub\Submission':
                    $source = Submission::find($feed->source_id);
                    if (!$source) {
                        $feed->delete();
                        break;
                    }
                    $tags = $source->tags()->pluck('tag_id')->toArray();
                    break;
                case 'App\Models\Readhub\Comment':
                case 'App\Models\Comment':
                    $comment = Comment::find($feed->source_id);
                    if (!$comment) {
                        $feed->delete();
                        break;
                    }
                    $source = $comment->source;
                    $tags = $source->tags()->pluck('tag_id')->toArray();
                    break;
            }
            if ($tags){
                $tags = array_unique($tags);
                $feed->tags = '';
                foreach ($tags as $tagId) {
                    $feed->tags.='['.$tagId.']';
                }
                $feed->save();
            }
        }
    }

}