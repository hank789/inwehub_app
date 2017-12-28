<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

class NewUserFollowingNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:newUserFollowingNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复用户关注通知数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notifications = Notification::where('type','App\Notifications\NewUserFollowing')->where('notification_type',Notification::NOTIFICATION_TYPE_NOTICE)->get();
        foreach ($notifications as $notification) {
            $data = $notification->data;
            $urls = explode('/',$data['url']);
            if (isset($urls[3])) {
                $user = User::where('uuid',$urls[3])->first();
                $data['avatar'] = $user->avatar;
                $notification->data = $data;
                $notification->save();
            }

        }
    }

}