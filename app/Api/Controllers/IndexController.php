<?php namespace App\Api\Controllers;
use App\Models\Activity\Coupon;
use App\Models\Article;
use App\Models\Attention;
use App\Models\Authentication;
use App\Models\Category;
use App\Models\Notice;
use App\Models\Readhub\Submission;
use App\Models\RecommendQa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/5/12 下午5:55
 * @email: wanghui@yonglibao.com
 */

class IndexController extends Controller {
    public function home(Request $request){
        $recommend_qa = Question::where('is_hot',1)->orderBy('id','desc')->get()->take(2);
        $host_question = [];
        foreach($recommend_qa as $question){
            /*已解决问题*/
            $bestAnswer = [];
            if($question->status >= 6 ){
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
            }
            $host_question[] = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'user_id' => $question->user_id,
                'description'  => $question->title,
                'hide' => $question->hide,
                'price' => $question->price,
                'status' => $question->status,
                'created_at' => (string)$question->created_at,
                'answer_user_id' => $bestAnswer ? $bestAnswer->user->id : '',
                'answer_username' => $bestAnswer ? $bestAnswer->user->name : '',
                'answer_user_title' => $bestAnswer ? $bestAnswer->user->title : '',
                'answer_user_company' => $bestAnswer ? $bestAnswer->user->company : '',
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->avatar : '',
                'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : ''
            ];
        }

        $recommend_expert_is_followed = 0;
        $recommend_expert_uid = Setting()->get('recommend_expert_uid',2);
        $recommend_expert_user = User::find($recommend_expert_uid);
        $user = $request->user();

        $expire_at = '';
        $is_expert = $user->userData->authentication_status;

        $expert_apply_status = 0;
        $expert_apply_tips = '点击前往认证';
        if(!empty($user->authentication)){
            if($user->authentication->status == 0){
                $expert_apply_status = 1;
                $expert_apply_tips = '认证处理中!';
            }elseif($user->authentication->status == 1){
                $expert_apply_status = 2;
                $expert_apply_tips = '身份已认证!';
            }else{
                $expert_apply_status = 3;
                $expert_apply_tips = '认证失败,重新认证';
            }
        }


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
        $show_ad = false;
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
            $item['comment_url'] = '/c/'.($read['category_id']).'/'.$read['slug'];
            $item['id'] = $read['id'];
            $recommend_read[] = $item;
        }

        $recommend_home_ac = Redis::connection()->hgetall('recommend_home_ac');
        if ($recommend_home_ac) {
            $recommend_home_ac_img = Redis::connection()->hgetall('recommend_home_ac_img');
            $recommend_ac_list = [];
            foreach ($recommend_home_ac as $ac_sort=>$ac_id) {
                $recommend_ac_list[$ac_sort] = Article::find($ac_id)->toArray();
                if (isset($recommend_home_ac_img[$ac_sort])) {
                    $recommend_ac_list[$ac_sort]['logo'] = $recommend_home_ac_img[$ac_sort];
                }
            }
        } else {
            $recommend_ac_list = Article::where('status','>',0)->orderBy('supports','DESC')->get()->take(3)->toArray();
        }

        $recommend_activity = [];
        foreach ($recommend_ac_list as $recommend_ac){
            $category = Category::find($recommend_ac['category_id']);

            $recommend_activity[] = [
              'id' => $recommend_ac['id'],
              'image_url' => $recommend_ac['logo'],
              'activity_type' => $category->slug == 'activity_enroll' ? 1 : 2
            ];
        }

        $data = [
            'recommend_expert_name' => Setting()->get('recommend_expert_name','郭小红'),//专家姓名
            'recommend_expert_description' => Setting()->get('recommend_expert_description','SAP咨询行业15年从业经历，熟悉离散制造行业，专注pp等模块，是一位非常自身的超级顾问'),//专家介绍
            'recommend_expert_uuid' => $recommend_expert_user->uuid,//专家uuid
            'recommend_expert_uid' => $recommend_expert_uid,//专家id
            'recommend_expert_is_followed' => $recommend_expert_is_followed,
            'recommend_expert_avatar_url' => $recommend_expert_user->getAvatarUrl(),//资深专家头像
            'recommend_qa' => $host_question,
            'first_ask_ac' => ['show_first_ask_coupon'=>$show_ad,'coupon_expire_at'=>$expire_at],
            'notices' => $notices,
            'recommend_experts' => $cache_experts,
            'recommend_read' => $recommend_read,
            'recommend_activity' => $recommend_activity,
            'is_expert' => $is_expert,
            'expert_apply_status' => $expert_apply_status,
            'expert_apply_tips' => $expert_apply_tips
        ];

        return self::createJsonData(true,$data);
    }
}