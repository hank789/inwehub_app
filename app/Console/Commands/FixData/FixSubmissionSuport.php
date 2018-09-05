<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */


use App\Models\Comment;
use App\Models\Submission;
use App\Models\Support;
use Illuminate\Console\Command;

class FixSubmissionSuport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:submission:support';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复文章点赞数';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $submissions = Submission::get();
        foreach ($submissions as $submission) {
            $tags = $submission->tags()->pluck('name')->toArray();
            $data = $submission->data;
            $data['keywords'] = implode(',',$tags);
            $submission->data = $data;
            $submission->save();
        }
    }

}