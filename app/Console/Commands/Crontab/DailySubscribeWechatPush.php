<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\System\Push;
use App\Models\User;
use App\Third\AliCdn\Cdn;
use Illuminate\Console\Command;

class DailySubscribeWechatPush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:daily:subscribe:wechat:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每日热点推荐微信推送';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //app推送
        $users = User::where('site_notifications','like','%"wechat_daily_subscribe":1%')->get();
        foreach ($users as $user) {
            event(new Push($user->id,'不容错过的今日热点推荐已新鲜出炉','点击查看',['object_type'=>'daily_subscribe','object_id'=>date('Y-m-d')]));
        }
    }

}