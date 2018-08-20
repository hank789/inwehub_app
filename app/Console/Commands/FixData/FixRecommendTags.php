<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Models\RecommendRead;
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
        $recommends = RecommendRead::get();
        foreach ($recommends as $recommend) {
            $recommend->setKeywordTags();
        }
    }

}