<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
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
            $support_count = Support::where('supportable_id',$submission->id)
                ->where('supportable_type',Submission::class)->count();
            $comment_count = Comment::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')->count();
            if ($submission->upvotes != $support_count || $submission->comments_number != $comment_count) {
                $submission->upvotes = $support_count;
                $submission->comments_number = $comment_count;
                $submission->save();
            }

        }
    }

}