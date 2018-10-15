<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\OperationNotify;
use App\Models\RecommendRead;
use App\Models\User;
use Illuminate\Console\Command;

class DailyRecommendReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:report:daily:recommend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '推荐文章报告';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $recommends = RecommendRead::where('audit_status',1)->whereBetween('created_at',[date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')])->count();
       $percent = bcadd($recommends/50 * 100,0,2);
       event(new OperationNotify('今日推荐完成率：'.$percent.'%（'.$recommends.'/50）'));
    }

}