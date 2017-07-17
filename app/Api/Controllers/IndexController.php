<?php namespace App\Api\Controllers;
use App\Models\Activity\Coupon;
use App\Models\Attention;
use App\Models\Notice;
use App\Models\RecommendQa;
use App\Models\User;
use Illuminate\Http\Request;

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
            $is_first_ask = !$user->userData->questions;
            //用户是否已经领过红包
            $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->first();
            if(!$coupon && $is_first_ask){
                $show_ad = true;
            }
            if($coupon && $coupon->expire_at > date('Y-m-d H:i:s'))
            {
                $expire_at = $coupon->expire_at;
            }
        }
        $notices = Notice::where('status',1)->orderBy('sort','DESC')->take(5)->get()->toArray();

        $data = [
            'recommend_expert_name' => Setting()->get('recommend_expert_name','郭小红'),//专家姓名
            'recommend_expert_description' => Setting()->get('recommend_expert_description','SAP咨询行业15年从业经历，熟悉离散制造行业，专注pp等模块，是一位非常自身的超级顾问'),//专家介绍
            'recommend_expert_uuid' => $recommend_expert_user->uuid,//专家uuid
            'recommend_expert_uid' => $recommend_expert_uid,//专家id
            'recommend_expert_is_followed' => $recommend_expert_is_followed,
            'recommend_expert_avatar_url' => $recommend_expert_user->getAvatarUrl(),//资深专家头像
            'recommend_qa' => $recommend_qa,
            'first_ask_ac' => ['show_first_ask_coupon'=>$show_ad,'coupon_expire_at'=>$expire_at],
            'notices' => $notices
        ];

        return self::createJsonData(true,$data);
    }
}