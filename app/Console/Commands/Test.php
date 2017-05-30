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
use Illuminate\Support\Facades\Cache;
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
        /*插入默认分类*/
        DB::table('categories')->insert([
            //问题分类
            ['id'=>20,'name' => '问题分类','slug'=>'question','parent_id' =>'0','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>21,'name' => 'SAP','slug'=>'question_sap','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>22,'name' => '业务类','slug'=>'question_business','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>23,'name' => '行业类','slug'=>'question_industry','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>24,'name' => 'Oracle','slug'=>'question_oracle','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>25,'name' => 'Microsoft','slug'=>'question_microsoft','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>26,'name' => 'Salesforce','slug'=>'question_salesforce','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>27,'name' => '金蝶','slug'=>'question_jindie','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>28,'name' => '用友','slug'=>'question_yongyou','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['id'=>29,'name' => '其他','slug'=>'question_other','parent_id' =>'2','grade'=>'1','sort' =>'0','status'=>'1','type'=>'questions,answers,tags','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
        ]);
        DB::table('tags')->insert([
            //问题分类 SAP tag
            ['name' => 'FI/CO','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'MM/SD','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'PP','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'PS','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'PM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'QM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'HR','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'WM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'EDI','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'HANA','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'Basis','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'BO/BW','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'Fiori','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'ABAP','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'EWM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'CRM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'PLM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'SCM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => 'SRM','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '其他','category_id'=>'21','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],

            //问题分类 业务类
            ['name' => '供应链','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '财务和成本','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '采购','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '仓储','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '物流和运输','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '销售','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '渠道分销','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '电商','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '生产','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '人力资源','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '商务','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '项目管理','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '进出口','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '其他','category_id'=>'22','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],

            ]);
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
