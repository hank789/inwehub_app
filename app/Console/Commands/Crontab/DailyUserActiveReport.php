<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\OperationNotify;
use App\Models\User;
use App\Services\MixpanelService;
use Illuminate\Console\Command;

class DailyUserActiveReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:report:daily:user-active';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户日活报告';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return;
        $data = MixpanelService::instance()->request(['events'],[
            'event' => ['inwehub:analysis:router:count'],
            'type'  => 'unique',
            'unit'  => 'day',
            'interval' => 1
        ]);
        $count = 0.1 * User::where('status',1)->count();
        $today = date('Y-m-d');
        $current = $data['data']['values']["inwehub:analysis:router:count"][$today];
        $percent = bcadd($current/$count * 100,0,2);
        event(new OperationNotify('今日日活完成率：'.$percent.'%（'.$current.'/'.$count.'）'));
    }

}