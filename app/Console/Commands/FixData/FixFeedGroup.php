<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */


use App\Models\Comment;
use App\Models\Feed\Feed;
use App\Models\Submission;
use Illuminate\Console\Command;

class FixFeedGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:feed_group';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复feed的圈子属性';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $feeds = Feed::get();
        foreach ($feeds as $feed) {
            if (str_contains($feed->data['feed_content'],'发布了文章')) {
                $data = $feed->data;
                $data['feed_content'] = str_replace('发布了文章','发布了分享',$data['feed_content']);
                $feed->data = $data;
                $feed->save();
            }
        }
    }

}