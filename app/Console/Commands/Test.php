<?php

namespace App\Console\Commands;

use App\Cache\UserCache;
use App\Events\Frontend\Expert\Recommend;
use App\Events\Frontend\System\Push;
use App\Models\Answer;
use App\Models\AppVersion;
use App\Models\Authentication;
use App\Models\Doing;
use App\Models\Pay\Order;
use App\Models\Pay\Settlement;
use App\Models\Pay\UserMoney;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\RecommendQa;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserInfo\JobInfo;
use App\Models\UserRegistrationCode;
use App\Models\UserTag;
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
        //$userTags = UserTag::leftJoin('user_data','user_tags.user_id','=','user_data.user_id')->where('user_data.authentication_status',1)->whereIn('user_tags.tag_id',[1,2,3])->where('user_tags.skills','>=','1')->toSql();
        DB::table('tags')->insert([
            //问题分类 行业类
            ['name' => '零售行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '消费品行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '批发分销行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '机械制造行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '高科技行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '汽车行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '航空与国防','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '公共部门','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '化工行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '石油天然气行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '采矿业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '公用事业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '轧制品行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '电信行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '金融与银行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '保险行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '工程建筑与运营','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '旅游与运输行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '国防与安全','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '医疗卫生行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '生命科学行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '媒体行业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '专业服务','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '高等教育与研究','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '体育与休闲娱乐业','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '互联网及信息技术','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
            ['name' => '其他','category_id'=>'23','logo' =>'','summary'=>'','parent_id'=>'0','followers'=>'0','created_at' => '2016-09-29 18:25:54','updated_at' => '2016-09-29 18:28:05'],
        ]);
        return;
        foreach($userTags as $uid){
            $toUser = User::find($uid);
            $invitation = QuestionInvitation::firstOrCreate(['user_id'=>$uid,'from_user_id'=>$question->user_id,'question_id'=>$question->id],[
                'from_user_id'=> $question->user_id,
                'question_id'=> $question->id,
                'user_id'=> $uid,
                'send_to'=> 'auto' //标示自动匹配
            ]);

            //已邀请
            $question->invitedAnswer();
            //记录动态
            TaskLogic::doing($question->user_id,'question_invite_answer_confirming',get_class($question),$question->id,$question->title,'');
            //记录任务
            TaskLogic::task($uid,get_class($question),$question->id,Task::ACTION_TYPE_ANSWER);
            //推送
            event(new Push($toUser,'您有新的回答邀请',$question->title,['object_type'=>'answer','object_id'=>$question->id]));
        }
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
