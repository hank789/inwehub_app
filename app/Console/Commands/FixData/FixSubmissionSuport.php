<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */


use App\Models\Comment;
use App\Models\Doing;
use App\Models\Scraper\Feeds;
use App\Models\Scraper\WechatMpInfo;
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
        $doings = Doing::where('action',Doing::ACTION_SHARE_SUBMISSION_SUCCESS)->get();
        foreach ($doings as $doing) {
            $number = Doing::where('action',Doing::ACTION_SHARE_SUBMISSION_SUCCESS)->where('source_id',$doing->source_id)->count();
            if ($number) {
                $submission = Submission::find($doing->source_id);
                $this->info($submission->id);
                $submission->share_number = $number;
                $submission->save();
            }
        }
        WechatMpInfo::where('group_id','>',0)->update(['group_id'=>0]);
        Feeds::where('group_id','>',0)->update(['group_id'=>0]);
    }

}