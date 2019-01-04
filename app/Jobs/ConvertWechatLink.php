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



class ConvertWechatLink implements ShouldQueue
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
        $submission = Submission::find($this->id);
        if (!$submission) return;
        $article = WechatWenzhangInfo::where('topic_id',$this->id)->first();
        if ($article->source_type == 1) {
            $author = WechatMpInfo::find($article->mp_id);
        } else {
            $author = Feeds::find($article->mp_id);
        }
        if (!$author) return;
        $url = $article->content_url;
        if ($article->source_type == 1) {
            if (str_contains($article->content_url,'wechat_redirect') || str_contains($article->content_url,'__biz=') || config('app.env') != 'production') {
                return;
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
                                dispatch(new ConvertWechatLink($this->id))->delay(Carbon::now()->addSeconds(60*5));
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
        $data = $submission->data;
        $data['url'] = $url;
        $submission->data = $data;
        $submission->save();

        Redis::connection()->hset('voten:submission:url',$url, $submission->id);

    }
}
