<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Notification;
use App\Models\Readhub\Category;
use App\Models\Readhub\ReadHubUser;
use App\Models\User;
use Illuminate\Console\Command;

class MoneyLogNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:moneyLogNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复资金通知数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notifications = Notification::where('notification_type',Notification::NOTIFICATION_TYPE_MONEY)->get();
        foreach ($notifications as $notification) {
            $data = $notification->data;
            $data['change_money'] = bcadd($data['change_money'],0,2);
            $data['before_money'] = bcadd($data['before_money'],0,2);
            $data['current_balance'] = bcadd($data['current_balance'],0,2);

            $notification->data = $data;
            $notification->save();
        }
    }

}