<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Scraper\Feeds;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Services\RateLimiter;
use App\Services\WechatGzhService;
use App\Traits\SubmitSubmission;
use Carbon\Carbon;
use function GuzzleHttp\Psr7\parse_query;
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
        $article = WechatWenzhangInfo::find($this->id);
        if ($article->topic_id > 0 || $article->status == 2) return;
        if ($article->source_type == 1) {
            $author = WechatMpInfo::find($article->mp_id);
        } else {
            $author = Feeds::find($article->mp_id);
        }
        if (!$author) return;
        //if ($author->group_id <= 0) return;
        $support_type = RateLimiter::instance()->hGet('article_support_type',$this->id);
        $user_id = $author->user_id;
        if ($article->source_type == 1) {
            if (str_contains($article->content_url,'wechat_redirect') || str_contains($article->content_url,'__biz=') || config('app.env') != 'production') {
                $url = $article->content_url;
            } else {
                $url = convertWechatTempLinkToForever($article->content_url);
                if (!$url) {
                    $url = WechatGzhService::instance()->foreverUrl($article->content_url);
                    if (!$url) {
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
                                ->send('解析微信公众号永久链接失败，稍后会继续尝试');
                            if ($unlimitUrl['error_code'] == 114) {
                                dispatch(new ArticleToSubmission($article->_id))->delay(Carbon::now()->addSeconds(60));
                            }
                            return;
                        }
                        $url = $unlimitUrl['data']['article_origin_url'];
                    }
                }
            }
            if (empty($author->qr_url)) {
                $parse_url = parse_url($url);
                $query = parse_query($parse_url['query']);
                $author->qr_url = $query['__biz'];
                $author->save();
            }
        } else {
            $url = $article->content_url;
        }

        $article->content_url = $url;
        $article->save();
        //检查url是否重复
        $exist_submission_id = Redis::connection()->hget('voten:submission:url',$url);
        if ($exist_submission_id){
            $article->delete();
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
        $article_description = strip_tags($article->description);
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
        $category = Category::where('slug','channel_xwdt')->first();
        $title = $article_description;
        if ($article->source_type != 1) {
            $title = str_limit($article_description,600);
        }
        $submission = Submission::create([
            'title'         => formatContentUrls($title),
            'slug'          => $this->slug($article->title),
            'type'          => 'link',
            'category_name' => $category->name,
            'category_id'   => $category->id,
            'group_id'      => $author->group_id,
            'public'        => $author->group_id?$author->group->public:1,
            'rate'          => firstRate(),
            'user_id'       => $user_id>0?$user_id:504,
            'support_type'  => $support_type?:1,
            'data'          => $data,
            'views'         => 1
        ]);
        $article->topic_id = $submission->id;
        $article->status = 2;
        $article->save();
        $author->group->increment('articles');
        (new NewSubmissionJob($submission->id,true))->handle();
        RateLimiter::instance()->sClear('group_read_users:'.$author->group_id);
        Redis::connection()->hset('voten:submission:url',$url, $submission->id);

    }
}
