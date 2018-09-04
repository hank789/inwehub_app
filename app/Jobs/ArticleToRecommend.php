<?php namespace App\Jobs;

use App\Models\Groups\Group;
use App\Models\RecommendRead;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Traits\SubmitSubmission;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class ArticleToRecommend implements ShouldQueue
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

    public $title;

    public $tagsId;

    public $tips;



    public function __construct($id, $title, $tagsId, $tips)
    {
        $this->id = $id;
        $this->title = $title;
        $this->tagsId = $tagsId;
        $this->tips = $tips;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $article = WechatWenzhangInfo::find($this->id);
        if ($article->topic_id <= 0) {
            (new ArticleToSubmission($this->id))->handle();
        }
        $article = WechatWenzhangInfo::find($this->id);
        $submission = Submission::find($article->topic_id);
        if (!$submission) return;
        $group = Group::find($submission->group_id);
        if (!$group->public) return;
        $oldData = $submission->data;
        unset($oldData['description']);
        unset($oldData['title']);
        $recommend = RecommendRead::firstOrCreate([
            'source_id' => $submission->id,
            'source_type' => get_class($submission)
        ],[
            'source_id' => $submission->id,
            'source_type' => get_class($submission),
            'tips' => $this->tips,
            'sort' => 0,
            'audit_status' => 0,
            'read_type' => RecommendRead::READ_TYPE_SUBMISSION,
            'created_at' => $submission->created_at,
            'updated_at' => Carbon::now(),
            'data' => array_merge($oldData, [
                'title' => $this->title?:$submission->title,
                'img'   => $submission->data['img'],
                'category_id' => $submission->category_id,
                'category_name' => $submission->category_name,
                'type' => $submission->type,
                'slug' => $submission->slug,
                'group_id' => $submission->group_id
            ])
        ]);
        if ($recommend->audit_status == 0) {
            $recommend->audit_status = 1;
            $recommend->sort = $recommend->id;
            $recommend->save();
            Tag::multiAddByIds($this->tagsId,$submission);
            if (isset($recommend->data['domain']) && $recommend->data['domain'] == 'mp.weixin.qq.com') {
                $info = getWechatArticleInfo($recommend->data['url']);
                if ($info['error_code'] == 0) {
                    $submission->views += $info['data']['article_view_count'];
                    $submission->upvotes += $info['data']['article_agree_count'];
                    $submission->calculationRate();
                }
            }
            $recommend->setKeywordTags();
        }

    }
}
