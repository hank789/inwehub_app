<?php namespace App\Console\Commands\Init;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */
use App\Models\Category;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\Translate;
use App\Services\RateLimiter;
use Illuminate\Console\Command;
use QL\Ext\PhantomJs;
use QL\QueryList;
use App\Traits\SubmitSubmission;

class CalcProductReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:calc:product-review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '计算产品的点评数据';

    protected $ql;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page = 1;
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
        $tagRels = $query->orderBy('id','desc')->simplePaginate(100,['*'],'page',$page);
        $tagIds = [];
        while ($tagRels->count() > 0) {
            foreach ($tagRels as $tagRel) {
                $submissions = Submission::where('category_id',$tagRel->tag_id)->where('status',1)->get();
                $count = 0;
                $rates = 0;
                foreach ($submissions as $submission) {
                    if (!is_array($submission->data['category_ids'])) {
                        $this->info($submission->id);
                    }
                    if (is_array($submission->data['category_ids']) && in_array($tagRel->category_id,$submission->data['category_ids'])) {
                        $count++;
                        $rates+=$submission->rate_star;
                    }
                }
                $tagRel->reviews = $count;
                $tagRel->review_rate_sum = $rates;
                $tagRel->review_average_rate = $count?bcdiv($rates,$count,1):0;
                $tagRel->save();
                $tagIds[$tagRel->tag_id] = $tagRel->tag_id;
            }
            $page ++;
            $tagRels = $query->orderBy('id','desc')->simplePaginate(100,['*'],'page',$page);
        }
        foreach ($tagIds as $tagId) {
            $info = Tag::getReviewInfo($tagId);
            Tag::where('id',$tagId)->update([
                'reviews' => $info['review_count']
            ]);
        }

    }

}