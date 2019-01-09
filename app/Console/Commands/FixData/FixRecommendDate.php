<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */


use App\Models\Comment;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\Support;
use Illuminate\Console\Command;

class FixRecommendDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:recommend:date';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复推荐阅读时间';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $page = 1;
        $query = Submission::where('type','!=','review')->where('status',1)->orderBy('id','desc');
        $reviewSubmissions = $query->simplePaginate(100,['*'],'page',$page);
        while ($reviewSubmissions->count() > 0) {
            foreach ($reviewSubmissions as $reviewSubmission) {
                $reviewSubmission->comments_number = $reviewSubmission->comments()->count();
                $reviewSubmission->calculationRate();
            }
            $page ++;
            $reviewSubmissions = $query->simplePaginate(100,['*'],'page',$page);
        }
    }

}