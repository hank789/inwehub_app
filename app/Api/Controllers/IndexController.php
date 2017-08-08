<?php namespace App\Api\Controllers;
use App\Models\Activity\Coupon;
use App\Models\Attention;
use App\Models\Authentication;
use App\Models\Notice;
use App\Models\Readhub\Submission;
use App\Models\RecommendQa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @author: wanghui
 * @date: 2017/5/12 下午5:55
 * @email: wanghui@yonglibao.com
 */

class IndexController extends Controller {
    public function home(Request $request){
        $recommend_qa = RecommendQa::select(['user_name','user_avatar_url','price','type','subject as description'])->where('status',1)->orderBy('sort','asc')->orderBy('updated_at','desc')->get()->toArray();
        if(empty($recommend_qa)){
            //推荐问答
            $recommend_qa = [
                [
                    "user_name"=> "隔壁老王",//提问者名字
                    "type"=> "1",//提问
                    "user_avatar_url"=> "http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/11/user_origin_10.jpg",//头像地址
                    "description"=> "老郭的花生卖多少钱?成本价多少?毛利率多少?赚钱么?我可以加盟吗?加盟费多少钱?",//问题内容
                    "price"=> "188"
                ],
                [
                    "user_name"=> "隔壁老王",//提问者名字
                    "type"=> "2",//回答
                    "user_avatar_url"=> "http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/media/16/user_origin_10.jpg",//头像地址
                    "description"=> "老郭的花生卖多少钱?成本价多少?毛利率多少?赚钱么?我可以加盟吗?加盟费多少钱?",//问题内容
                    "price"=> "188"
                ]
            ];
        }
        $recommend_expert_is_followed = 0;
        $recommend_expert_uid = Setting()->get('recommend_expert_uid',2);
        $recommend_expert_user = User::find($recommend_expert_uid);
        $user = $request->user();

        $show_ad = false;
        $expire_at = '';
        if($user){
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($recommend_expert_user))->where('source_id','=',$recommend_expert_uid)->first();
            if ($attention){
                $recommend_expert_is_followed = 1;
            }
            //检查活动时间
            $ac_first_ask_begin_time = Setting()->get('ac_first_ask_begin_time');
            $ac_first_ask_end_time = Setting()->get('ac_first_ask_end_time');
            if($ac_first_ask_begin_time && $ac_first_ask_end_time && $ac_first_ask_begin_time<=date('Y-m-d H:i') && $ac_first_ask_end_time>date('Y-m-d H:i')){
                $is_first_ask = !$user->userData->questions;
                //用户是否已经领过红包
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->first();
                if(!$coupon && $is_first_ask){
                    $show_ad = true;
                }
                if($coupon && $coupon->coupon_status == Coupon::COUPON_STATUS_PENDING  && $coupon->expire_at > date('Y-m-d H:i:s'))
                {
                    $expire_at = $coupon->expire_at;
                }
            }
        }
        $notices = Notice::where('status',1)->orderBy('sort','DESC')->take(5)->get()->toArray();

        //随机7个专家
        $cache_experts = Cache::get('home_experts');
        if (!$cache_experts){
            $experts = Authentication::where('status',1)->pluck('user_id')->toArray();
            shuffle($experts);
            $cache_experts = [];
            $expert_uids = array_slice($experts,0,7);
            foreach ($expert_uids as $key=>$expert_uid) {
                $expert_user = User::find($expert_uid);
                $cache_experts[$key]['name'] = $expert_user->name;
                $cache_experts[$key]['title'] = $expert_user->title;
                $cache_experts[$key]['uuid'] = $expert_user->uuid;
                $cache_experts[$key]['work_years'] = $expert_user->getWorkYears();
                $cache_experts[$key]['avatar_url'] = $expert_user->avatar;
            }
            Cache::put('home_experts',$cache_experts,60*24);
        }

        //推荐阅读
        $recommend_list = Submission::where('recommend_status',Submission::RECOMMEND_STATUS_PUBLISH)->orderBy('recommend_sort','desc')->get()->take(5)->toArray();

        $recommend_read = [];
        foreach ($recommend_list as $read){
            $item = [];
            $item['title'] = $read['title'];
            $item['img_url'] = $read['data']['img']??'';
            $item['publish_at'] = date('Y/m/d H:i',strtotime($read['created_at']));
            $item['upvotes'] = $read['upvotes'];
            $item['view_url'] = $read['data']['url'];
            $item['id'] = $read['id'];
            $recommend_read[] = $item;
        }

        $data = [
            'recommend_expert_name' => Setting()->get('recommend_expert_name','郭小红'),//专家姓名
            'recommend_expert_description' => Setting()->get('recommend_expert_description','SAP咨询行业15年从业经历，熟悉离散制造行业，专注pp等模块，是一位非常自身的超级顾问'),//专家介绍
            'recommend_expert_uuid' => $recommend_expert_user->uuid,//专家uuid
            'recommend_expert_uid' => $recommend_expert_uid,//专家id
            'recommend_expert_is_followed' => $recommend_expert_is_followed,
            'recommend_expert_avatar_url' => $recommend_expert_user->getAvatarUrl(),//资深专家头像
            'recommend_qa' => $recommend_qa,
            'first_ask_ac' => ['show_first_ask_coupon'=>$show_ad,'coupon_expire_at'=>$expire_at],
            'notices' => $notices,
            'recommend_experts' => $cache_experts,
            'recommend_read' => $recommend_read
        ];

        return self::createJsonData(true,$data);
    }
}