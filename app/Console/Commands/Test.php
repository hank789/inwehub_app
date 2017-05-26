<?php

namespace App\Console\Commands;

use App\Cache\UserCache;
use App\Events\Frontend\Expert\Recommend;
use App\Events\Frontend\System\Push;
use App\Models\Answer;
use App\Models\AppVersion;
use App\Models\Authentication;
use App\Models\Pay\Order;
use App\Models\Pay\Settlement;
use App\Models\Pay\UserMoney;
use App\Models\Question;
use App\Models\RecommendQa;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserInfo\JobInfo;
use App\Models\UserRegistrationCode;
use App\Services\City\CityData;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Getui;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Payment\Client\Charge;
use Payment\Common\PayException;
use Payment\Config;
use Illuminate\Support\Facades\DB;


class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $s = UserCache::getUserInfoCache(1);
        var_dump($s);
        return;
        $payData = [
            'body'    => 'test',
            'subject'    => 'test',
            'order_no'    => time(),
            'timeout_express' => time() + 600,// 表示必须 600s 内付款
            'amount'    => 100,// 微信沙箱模式，需要金额固定为3.01
            'return_param' => 123,
            'pay_channel'  => 'we',
            'client_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',// 客户地址
        ];

        $order = Order::create($payData);

        var_dump($order->id);
        return;
        $head_img_url = 'http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/expert/recommend/1/667ba35683a2c99646fccfb84209740d.png';
        $data['name'] = '张三';
        $data['gender'] = '男';
        $data['industry_tags'] = '工业';
        $data['work_years'] = '10';
        $data['mobile'] = '15050368485';
        $data['description'] = '你好';
        event(new Recommend(1,$data['name'],$data['gender'],$data['industry_tags'],$data['work_years'],$data['mobile'],$data['description'],$head_img_url));

        /*$devices = UserDevice::where('user_id',2)->get();

        $data = [
            'title' => 'hello',
            'body'  => 'body:nihao',
            'content' => '{payload:"通知去干嘛这里可以自定义"}',
            'text'=>'text:这是内容',
            'payload' => '{title:"title",content:"content",payload:"ppppp"}'
        ];
        event(new Push(User::find(2),'有人向您发起了回答邀请',
            'content:问题内容,有人向您发起了回答邀请,有人向您发起了回答邀请,有人向您发起了回答邀请',['payload'=>['object_type'=>'question','object_id'=>123]],[],1));*/
    }
}
