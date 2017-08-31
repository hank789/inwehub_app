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

class ReadhubNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:readhubNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复阅读站通知';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notifications = Notification::where('notification_type',Notification::NOTIFICATION_TYPE_READ)->get();
        $categories = Category::all();
        $list = [];
        foreach ($categories as $category) {
            $list[$category->id] = $category->name;
        }
        foreach ($notifications as $notification) {
            $data = $notification->data;
            foreach ($list as $nid=>$nname) {
                if (mb_strpos($data['url'],$nname)){
                    $data['url'] = str_replace('/'.$nname.'/','/'.$nid.'/',$data['url']);
                    $notification->data = $data;
                    $notification->save();
                    continue;
                }
            }
        }
    }

}