<?php namespace App\Console\Commands;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Answer;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\TagCategoryRel;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Sitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成sitemap文件';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $sitemap = \App::make("sitemap");
        $count = 0;
        $date = $this->argument('date');
        //$date = '2018-12-03 00:00:00';
        if (!$date) {
            $date = '2016-12-12 00:00:00';
        }
        $this->info($date);
        $urls = [];

        /*$questions = Question::where('is_recommend',1)->where('question_type',1)->orWhere('question_type',2)->orderBy('id','desc')->get();
        foreach ($questions as $question) {
            $count++;
            $url = 'https://www.inwehub.com/askCommunity/interaction/answers/'.$question->id;
            $sitemap->add($url, (new Carbon($question->created_at))->toAtomString(), '1.0', 'monthly');
            if (strtotime($question->created_at) >= strtotime($date)) {
                $urls[] = $url;
            }
            if ($question->question_type == 2) {
                $answers = Answer::where('question_id',$question->id)->where('status',1)->get();
                foreach ($answers as $answer) {
                    $count++;
                    $url = 'https://www.inwehub.com/askCommunity/interaction/'.$answer->id;
                    $sitemap->add($url, (new Carbon($answer->created_at))->toAtomString(), '1.0', 'monthly');
                    if (strtotime($answer->created_at) >= strtotime($date)) {
                        $urls[] = $url;
                    }
                }
            }
        }

        $recommendReads = RecommendRead::where('audit_status',1)->orderBy('id','desc')->get();
        foreach ($recommendReads as $recommendRead) {
            switch ($recommendRead->read_type) {
                case RecommendRead::READ_TYPE_SUBMISSION:
                    $count++;
                    $submission = Submission::find($recommendRead->source_id);
                    $url = 'https://www.inwehub.com/c/'.$submission->category_id.'/'.$submission->slug;
                    $sitemap->add($url, (new Carbon($submission->created_at))->toAtomString(), '1.0', 'monthly');
                    if (strtotime($recommendRead->created_at) >= strtotime($date)) {
                        $urls[] = $url;
                    }
                    break;
            }

        }*/

        //点评产品详情
        $page = 1;
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->orderBy('tag_id','desc');
        $tags = $query->simplePaginate(100,['*'],'page',$page);
        $tagIds = [];
        while ($tags->count() > 0) {
            foreach ($tags as $tag) {
                if (isset($tagIds[$tag->tag_id])) continue;
                $tagIds[$tag->tag_id] = $tag->tag_id;
                $count++;
                $url = 'https://www.inwehub.com/dianping/product/'.rawurlencode($tag->tag->name);
                $sitemap->add($url, (new Carbon($tag->tag->created_at))->toAtomString(), '1.0', 'monthly');
                if (strtotime($tag->tag->created_at) >= strtotime($date)) {
                    $urls[] = $url;
                }
            }
            $page ++;
            $tags = $query->simplePaginate(100,['*'],'page',$page);
        }
        //点评详情
        $page = 1;
        $query = Submission::where('type','review')->where('status',1)->orderBy('id','desc');
        $reviewSubmissions = $query->simplePaginate(100,['*'],'page',$page);
        while ($reviewSubmissions->count() > 0) {
            foreach ($reviewSubmissions as $reviewSubmission) {
                $count++;
                $url = 'https://www.inwehub.com/dianping/comment/'.$reviewSubmission->slug;
                $sitemap->add($url, (new Carbon($reviewSubmission->created_at))->toAtomString(), '1.0', 'monthly');
                if (strtotime($reviewSubmission->created_at) >= strtotime($date)) {
                    $urls[] = $url;
                }
            }
            $page ++;
            $reviewSubmissions = $query->simplePaginate(100,['*'],'page',$page);
        }

        $sitemap->store('xml', 'sitemap');
        $this->info('共生成地址：'.$count);
        $newUrls = array_chunk($urls,2000);
        foreach ($newUrls as $newUrl) {
            $result = submitUrlsToSpider($newUrl);
            var_dump($result);
        }
    }

}