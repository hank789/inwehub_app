<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\ContentCollection;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;



class UpdateProductInfoCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $tag_id;



    public function __construct($tag_id)
    {
        $this->tag_id = $tag_id;
    }

    /**
     * Execute the job.
     *
     * @return array
     */
    public function handle()
    {
        $tag = Tag::find($this->tag_id);
        $reviewInfo = Tag::getReviewInfo($tag->id);
        $data = $tag->toArray();
        $data['review_count'] = $reviewInfo['review_count'];
        $data['review_average_rate'] = $reviewInfo['review_average_rate'];
        $submissions = Submission::selectRaw('count(*) as total,rate_star')->where('status',1)->where('category_id',$tag->id)->groupBy('rate_star')->get();
        foreach ($submissions as $submission) {
            $data['review_rate_info'][] = [
                'rate_star' => $submission->rate_star,
                'count'=> $submission->total
            ];
        }

        $data['related_tags'] = $tag->relationReviews(8);
        $categoryRels = TagCategoryRel::where('tag_id',$tag->id)->where('type',TagCategoryRel::TYPE_REVIEW)->orderBy('support_rate','desc')->get();
        $cids = [];
        foreach ($categoryRels as $key=>$categoryRel) {
            $cids[] = $categoryRel->category_id;
            $category = Category::find($categoryRel->category_id);
            if ($category->type != 'product_album') continue;//只显示专辑
            $rate = TagCategoryRel::where('category_id',$category->id)->where('support_rate','>',$categoryRel->support_rate)->count();
            $support_rate = TagCategoryRel::where('category_id',$category->id)->sum('support_rate');
            $data['categories'][] = [
                'id' => $category->id,
                'name' => $category->name,
                'rate' => $rate+1,
                'support_rate' => $support_rate,
                'type' => $category->type == 'enterprise_review'?1:2
            ];
        }
        //产品介绍封面图
        $data['cover_pic'] = $tag->getCoverPic();
        //产品亮点轮播图
        $introduce_pic = $tag->getIntroducePic();
        if ($introduce_pic) {
            usort($introduce_pic,function ($a,$b) {
                if ($a['sort'] == $b['sort']) {
                    return 0;
                }
                return ($a['sort'] < $b['sort']) ? -1 : 1;
            });
            $data['introduce_pic'] = array_column($introduce_pic,'url');
        }
        //产品最新资讯
        $data['recent_news'] = [];
        $news = WechatWenzhangInfo::where('source_type',1)
            ->where('type',WechatWenzhangInfo::TYPE_TAG_NEWS)
            ->whereHas('tags',function($query) use ($tag) {
                $query->where('tag_id', $tag->id)->where('is_display',1);
            })
            ->orderBy('date_time','desc')->take(5)->get();
        foreach ($news as $new) {
            $data['recent_news'][] = [
                'title' => strip_tags($new->title),
                'date' => date('Y年m月d日',strtotime($new->date_time)),
                'author' => domain($new->content_url),
                'cover_pic' => $new->cover_url,
                'link_url' => config('app.url').'/articleInfo/'.$new->_id.'?inwehub_user_device=weapp_dianping&source=product_'.$tag->id
            ];
        }
        //产品案例介绍
        $data['case_list'] = [];
        $caseList = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_SHOW_CASE)
            ->where('source_id',$tag->id)->where('status',1)->orderBy('sort','desc')->get();
        foreach ($caseList as $case) {
            $link_url = $case->content['link_url'];
            if (!str_contains($link_url,'&source=product_') && $case->content['type'] == 'link') {
                $link_url .= '&source=product_'.$case->source_id;
            }
            $data['case_list'][] = [
                'id' => $case->id,
                'title' => $case->content['title'],
                'desc' => $case->content['desc'],
                'cover_pic' => $case->content['cover_pic'],
                'type' => $case->content['type'],
                'link_url' => $link_url
            ];
        }
        //产品专家观点
        $data['expert_review'] = [];
        $ideaList = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_EXPERT_IDEA)
            ->where('source_id',$tag->id)->where('status',1)->orderBy('sort','desc')->get();
        foreach ($ideaList as $idea) {
            $data['expert_review'][] = [
                'avatar' => $idea->content['avatar'],
                'name' => $idea->content['name'],
                'title' => $idea->content['title'],
                'content' => $idea->content['content']
            ];
        }
        $tag->setProductCacheInfo($data);
        return $data;
    }
}
