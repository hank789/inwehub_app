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

class FollowerNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:followerNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复关注通知';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notifications = Notification::where('notification_type',Notification::NOTIFICATION_TYPE_NOTICE)->get();
        foreach ($notifications as $notification) {
            $data = $notification->data;
            if (0 === mb_strpos($data['url'],'/share/resume')){
                $data['url'] = str_replace('?id=','/',$data['url']);
                $notification->data = $data;
                $notification->save();
            }
        }
    }

}