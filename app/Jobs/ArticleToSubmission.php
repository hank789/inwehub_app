<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Scraper\Feeds;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\RateLimiter;
use App\Services\WechatGzhService;
use App\Traits\SubmitSubmission;
use Carbon\Carbon;
use function GuzzleHttp\Promise\is_fulfilled;
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
        $url = $article->content_url;
        if ($article->source_type == 1) {
            if (str_contains($article->content_url,'wechat_redirect') || str_contains($article->content_url,'__biz=') || str_contains($article->content_url,'/s/') || config('app.env') != 'production') {
                $url = $article->content_url;
            } elseif ($author->group_id > 0) {
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
            if (empty($author->qr_url) && false) {
                $parse_url = parse_url($url);
                $query = parse_query($parse_url['query']);
                $author->qr_url = $query['__biz'];
                $author->save();
            }
        }

        $article->content_url = $url;
        $article->save();
        //检查url是否重复
        $exist_submission_id = Redis::connection()->hget('voten:submission:url',$url);
        if ($exist_submission_id){
            $article->delete();
            return;
        }
        if (empty($article->cover_url)) {
            $info = getUrlInfo($article->content_url,true, 'submissions', false);
            $img_url = $info['img_url'];
        } else {
            $parse_url = parse_url($article->cover_url);
            $img_url = $article->cover_url;
            //非本地地址，存储到本地
            if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                $img_url = saveImgToCdn($article->cover_url,'submissions', false, false);
            }
        }
        if ($img_url == 'https://cdn.inwehub.com/system/group_18@3x.png') {
            $img_url = '';
        }

        if ($img_url) {
            $article->cover_url = $img_url;
            $article->save();
        }
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
        if ($author->group_id) {
            $author->group->increment('articles');
        }
        $regionTags = $author->tags->pluck('id')->toArray();
        $productTags = $article->tags->pluck('id')->toArray();
        $regionTags = array_merge($regionTags,$productTags);
        foreach ($productTags as $productTag) {
            $rels = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->get();
            foreach ($rels as $rel) {
                $category = Category::find($rel->category_id);
                //专辑对应的领域
                if ($category && $category->parent_id == 1359) {
                    $regionTags[] = $category->getRegionTag();
                }
            }
        }
        if($regionTags) {
            Tag::multiAddByIds($regionTags,$submission);
        }
        (new NewSubmissionJob($submission->id,true))->handle();
        RateLimiter::instance()->sClear('group_read_users:'.$author->group_id);
        Redis::connection()->hset('voten:submission:url',$url, $submission->id);

    }
}
