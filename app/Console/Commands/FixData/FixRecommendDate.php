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
        $recommends = RecommendRead::where('rate','<=',0)->get();
        foreach ($recommends as $recommend) {
            $recommend->rate = $recommend->getRateWeight() + $recommend->source->rate;
            $recommend->save();
        }
    }

}