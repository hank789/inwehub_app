<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
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
            switch ($feed->source_type) {
                case 'App\Models\Question':
                    continue;
                    break;
                case 'App\Models\Answer':
                    continue;
                    break;
                case 'App\Models\Submission':
                case 'App\Models\Readhub\Submission':
                    $source = Submission::find($feed->source_id);
                    if (!$source) {
                        $feed->delete();
                        continue;
                    }
                    $feed->group_id = $source->group_id;
                    $feed->public = $source->public;
                    $feed->save();
                    break;
                case 'App\Models\Readhub\Comment':
                case 'App\Models\Comment':
                    $comment = Comment::find($feed->source_id);
                    if (!$comment) {
                        $feed->delete();
                        continue;
                    }
                    break;
            }
            if (str_contains($feed->data['feed_content'],'互动问答') || str_contains($feed->data['feed_content'],'专业问答')) {
                $data = $feed->data;
                if (in_array($feed->feed_type,[
                    Feed::FEED_TYPE_FOLLOW_FREE_QUESTION,
                    Feed::FEED_TYPE_UPVOTE_PAY_QUESTION,
                    Feed::FEED_TYPE_UPVOTE_FREE_QUESTION
                ])) {
                    $data = ['feed_content'=>$feed->data['feed_content']];
                }
                if ($feed->feed_type == Feed::FEED_TYPE_CREATE_FREE_QUESTION) {
                    $data = [
                        'feed_content'=>$feed->data['feed_content'],
                        'question_title' => $feed->data['question_title']
                    ];
                }
                $data['feed_content'] = str_replace('互动问答','问答',$data['feed_content']);
                $data['feed_content'] = str_replace('专业问答','问答',$data['feed_content']);
                $feed->data = $data;
                $feed->save();
            }
            if ($feed->feed_type == Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE) {
                $data = [
                    'submission_title'=>$feed->data['submission_title'],
                    'feed_content' => $feed->data['feed_content']
                ];
                $feed->data = $data;
                $feed->save();
            }
            if (in_array($feed->feed_type,[
                Feed::FEED_TYPE_FOLLOW_USER,
                Feed::FEED_TYPE_COMMENT_FREE_QUESTION,
                Feed::FEED_TYPE_UPVOTE_READHUB_ARTICLE,
                Feed::FEED_TYPE_COMMENT_READHUB_ARTICLE,
                Feed::FEED_TYPE_COMMENT_PAY_QUESTION,
                Feed::FEED_TYPE_CREATE_PAY_QUESTION
            ])) {
                $feed->delete();
            }
        }
    }

}