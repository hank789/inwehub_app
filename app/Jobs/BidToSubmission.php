<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Groups\Group;
use App\Models\Scraper\BidInfo;
use App\Models\Submission;
use App\Services\RateLimiter;
use App\Traits\SubmitSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;



class BidToSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SubmitSubmission;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $timeout = 180;

    public $id;



    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $article = BidInfo::find($this->id);
        if ($article->topic_id > 0 || $article->status == 2) return;
        $groups = $article->detail['group_ids'];
        if (!$groups) return;
        $group = Group::find($groups[0]);
        $support_type = RateLimiter::instance()->hGet('bid_support_type',$this->id);
        $url = $article->source_url;
        //检查url是否重复
        $exist_submission_id = Redis::connection()->hget('voten:submission:url',$url);
        if ($exist_submission_id){
            $article->delete();
            return;
        }

        $img_url = 'https://cdn.inwehub.com/groups/2018/09/1537334960wpnKTwa.png';
        $article_description = $article->detail['bid_html_body'];
        $data = [
            'url'           => $url,
            'title'         => $article->title,
            'description'   => $article_description,
            'type'          => 'link',
            'embed'         => null,
            'img'           => $img_url,
            'thumbnail'     => null,
            'providerName'  => null,
            'publishedTime' => null,
            'domain'        => domain($url),
        ];
        Redis::connection()->hset('voten:submission:url',$url,1);

        $data['current_address_name'] = '';
        $data['current_address_longitude'] = '';
        $data['current_address_latitude'] = '';
        $data['mentions'] = [];
        $category = Category::where('slug','bid_info')->first();
        $titleTip = ' <br>'.($article->area?'地区：'.$article->area.' <br>':'').($article->subtype?'类型：'.$article->subtype.' <br>':'').'发布时间：'.date('m月d号',strtotime($article->publishtime));
        $submission = Submission::create([
            'title'         => $article->title.$titleTip,
            'slug'          => $this->slug($article->title),
            'type'          => 'link',
            'category_name' => $category->name,
            'category_id'   => $category->id,
            'group_id'      => $group->id,
            'public'        => $group->public,
            'rate'          => firstRate(),
            'user_id'       => $group->user_id,
            'support_type'  => $support_type?:1,
            'data'          => $data,
            'views'         => 1
        ]);
        $article->topic_id = $submission->id;
        $article->status = 2;
        $article->save();
        $group->increment('articles');
        (new NewSubmissionJob($submission->id))->handle();
        RateLimiter::instance()->sClear('group_read_users:'.$group->id);
        Redis::connection()->hset('voten:submission:url',$url, $submission->id);

    }
}
