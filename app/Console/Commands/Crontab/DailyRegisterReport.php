<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\OperationNotify;
use App\Models\User;
use Illuminate\Console\Command;

class DailyRegisterReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:report:daily:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '注册用户报告';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $yesterday1 = User::where('status',1)->whereBetween('created_at',[date('Y-m-d 00:00:00',strtotime('-1 day')),date('Y-m-d 23:59:59',strtotime('-1 day'))])->count();
        $yesterday2 = User::where('status',1)->whereBetween('created_at',[date('Y-m-d 00:00:00',strtotime('-2 day')),date('Y-m-d 23:59:59',strtotime('-2 day'))])->count();
        $message = '新用户：昨日新增'.$yesterday1.'('.bcadd(($yesterday1-$yesterday2)/($yesterday2?:1)*100,0,2).'%)';
        $weekdays1 = User::where('status',1)->whereBetween('created_at',[date('Y-m-d 00:00:00',(time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)),date('Y-m-d 23:59:59',(time() + (7 - (date('w') == 0 ? 7 : date('w'))) * 24 * 3600))])->count();
        $weekdays2 = User::where('status',1)->whereBetween('created_at',[date('Y-m-d 00:00:00',strtotime('last monday',strtotime('-6 days'))),date('Y-m-d 23:59:59',strtotime('last sunday'))])->count();
        $message .= '|本周新增'.$weekdays1.'('.bcadd(($weekdays1-$weekdays2)/($weekdays2?:1)*100,0,2).'%)';
        $month1 = User::where('status',1)->whereBetween('created_at',[date('Y-m-01 00:00:00'),date('Y-m-d 23:59:59',strtotime(date('Y-m-01',strtotime('+1 month')).' -1 day'))])->count();
        $month2 = User::where('status',1)->whereBetween('created_at',[date('Y-m-01 00:00:00',strtotime('-1 month')),date('Y-m-d 23:59:59',strtotime(date('Y-m-01').' -1 day'))])->count();
        $message .= '|本月新增'.$month1.'('.bcadd(($month1-$month2)/($month2?:1)*100,0,2).'%)';

        event(new OperationNotify($message));
    }

}