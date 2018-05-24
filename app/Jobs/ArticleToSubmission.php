<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Services\RateLimiter;
use App\Traits\SubmitSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;



class ArticleToSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SubmitSubmission;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

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
        $article = WechatWenzhangInfo::find($this->id);
        if ($article->topic_id > 0) return;
        $author = WechatMpInfo::find($article->mp_id);
        if (!$author) return;
        if ($author->group_id <= 0) return;
        $unlimitUrl = convertWechatLimitLinkToUnlimit($article->content_url,$author->wx_hao);
        if ($unlimitUrl['error_code'] != 0) {
            $fileds = [
                [
                    'title' => '返回结果',
                    'value' => json_encode($unlimitUrl, JSON_UNESCAPED_UNICODE)
                ]
            ];
            //调用失败
            \Slack::to(config('slack.ask_activity_channel'))
                ->attach(
                    [
                        'fields' => $fileds
                    ]
                )
                ->send('解析微信公众号永久链接失败');
            return;
        }
        $url = $unlimitUrl['data']['article_origin_url'];
        $article->content_url = $url;
        $article->save();
        //检查url是否重复
        $exist_submission_id = Redis::connection()->hget('voten:submission:url',$url);
        if ($exist_submission_id){
            return;
        }

        $parse_url = parse_url($article->cover_url);
        $img_url = $article->cover_url;
        //非本地地址，存储到本地
        if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
            $file_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
            Storage::disk('oss')->put($file_name,file_get_contents($article->cover_url));
            $img_url = Storage::disk('oss')->url($file_name);
        }
        $article->cover_url = $img_url;
        $article->save();

        $data = [
            'url'           => $url,
            'title'         => $article->title,
            'description'   => $article->description,
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
        $category = Category::where('slug','channel_xwdt')->first();

        $submission = Submission::create([
            'title'         => formatContentUrls($article->title),
            'slug'          => $this->slug($article->title),
            'type'          => 'link',
            'category_name' => $category->name,
            'category_id'   => $category->id,
            'group_id'      => $author->group_id,
            'public'        => $author->group->public,
            'rate'          => firstRate(),
            'user_id'       => config('app.env') != 'production'?1:504,
            'data'          => $data,
        ]);
        $article->topic_id = $submission->id;
        $article->save();
        $author->group->increment('articles');
        RateLimiter::instance()->sClear('group_read_users:'.$author->group->id);
        Redis::connection()->hset('voten:submission:url',$url, $submission->id);

    }
}
