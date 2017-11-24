<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Models\Submission;
use Illuminate\Console\Command;
use App\Models\Readhub\Submission as ReadhubSubmission;

class FixSubmissionIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:submission:ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复文章id';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $submissions = ReadhubSubmission::get();
        foreach ($submissions as $submission) {
            $new_submission = Submission::where('slug',$submission->slug)->first();
            $new_submission->id = $submission->id;
            $new_submission->save();
        }
    }

}