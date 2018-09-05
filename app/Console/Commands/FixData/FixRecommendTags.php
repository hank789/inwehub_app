<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */


use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Submission;
use Illuminate\Console\Command;

class FixRecommendTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:recommend:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复推荐阅读标签';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $submissions = Submission::where('status',1)->get();
        foreach ($submissions as $submission) {
            $submission->setKeywordTags();
        }
        $questions = Question::get();
        foreach ($questions as $question) {
            $question->setKeywordTags();
        }
        $recommends = RecommendRead::get();
        foreach ($recommends as $recommend) {
            $recommend->setKeywordTags();
        }
    }

}