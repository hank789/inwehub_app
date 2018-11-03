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
        $tagRels = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->get();
        $tagIds = [];
        foreach ($tagRels as $tagRel) {
            $submissions = Submission::where('category_id',$tagRel->category_id)->get();
            $count = 0;
            $rates = 0;
            foreach ($submissions as $submission) {
                if (in_array($tagRel->category_id,$submission->data['category_ids'])) {
                    $count++;
                    $rates+=$submission->rate_star;
                }
            }
            $tagRel->reviews = $count;
            $tagRel->review_rate_sum = $rates;
            $tagRel->review_average_rate = bcdiv($rates,$count,1);
            $tagRel->save();
            $tagIds[$tagRel->tag_id] = $tagRel->tag_id;
        }
        foreach ($tagIds as $tagId) {
            $info = Tag::getReviewInfo($tagId);
            Tag::where('id',$tagId)->update([
                'reviews' => $info['review_count']
            ]);
        }

    }

}