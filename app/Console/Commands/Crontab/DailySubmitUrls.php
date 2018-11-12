<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\OperationNotify;
use App\Models\Answer;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\User;
use App\Services\MixpanelService;
use Illuminate\Console\Command;

class DailySubmitUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:submit:daily:urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '提交链接给搜索引擎';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = date('Y-m-d H:i:s',strtotime('-24 hours'));
        $recommendReads = RecommendRead::where('audit_status',1)->where('created_at','>=',$today)->get();
        $urls = [];
        foreach ($recommendReads as $recommendRead) {
            switch ($recommendRead->read_type) {
                case RecommendRead::READ_TYPE_SUBMISSION:
                    $submission = Submission::find($recommendRead->source_id);
                    $url = 'https://www.inwehub.com/c/'.$submission->category_id.'/'.$submission->slug;
                    $urls[] = $url;
                    break;
            }

        }
        $questions = Question::where('is_recommend',1)->where('question_type',1)->where('created_at','>=',$today)->get();
        foreach ($questions as $question) {
            $url = 'https://www.inwehub.com/askCommunity/interaction/answers/'.$question->id;
            $urls[] = $url;
        }
        $answers = Answer::where('status',1)->where('created_at','>=',$today)->get();
        foreach ($answers as $answer) {
            if ($answer->question->question_type == 1) continue;
            $url = 'https://www.inwehub.com/askCommunity/interaction/'.$answer->id;
            $urls[] = $url;
        }

        $result = submitUrlsToSpider($urls);
        var_dump($result);
    }

}