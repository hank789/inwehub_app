<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */
use App\Models\Category;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use Illuminate\Console\Command;

class FixAutoTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:auto:tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复自动标签';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $submissions = Submission::where('status',1)->where('type','article')->get();
        foreach ($submissions as $submission) {
            $submission->setKeywordTags();
        }
    }

}