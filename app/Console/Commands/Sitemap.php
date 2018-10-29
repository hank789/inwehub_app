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
use Carbon\Carbon;
use Illuminate\Console\Command;

class Sitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

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
        $recommendReads = RecommendRead::where('audit_status',1)->get();
        foreach ($recommendReads as $recommendRead) {
            switch ($recommendRead->read_type) {
                case RecommendRead::READ_TYPE_SUBMISSION:
                    $submission = Submission::find($recommendRead->source_id);
                    $url = config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug;
                    $sitemap->add($url, (new Carbon($submission->created_at))->toAtomString(), '1.0', 'monthly');
                    break;
            }

        }
        $questions = Question::where('is_recommend',1)->where('question_type',1)->orWhere('question_type',2)->get();
        foreach ($questions as $question) {
            $url = config('app.mobile_url').'#/askCommunity/interaction/answers/'.$question->id;
            $sitemap->add($url, (new Carbon($question->created_at))->toAtomString(), '1.0', 'monthly');
            if ($question->question_type == 2) {
                $answers = Answer::where('question_id',$question->id)->where('status',1)->get();
                foreach ($answers as $answer) {
                    $url = config('app.mobile_url').'#/askCommunity/interaction/'.$answer->id;
                    $sitemap->add($url, (new Carbon($answer->created_at))->toAtomString(), '1.0', 'monthly');
                }
            }
        }

        $sitemap->store('xml', 'sitemap');
    }

}