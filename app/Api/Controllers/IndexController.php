<?php namespace App\Api\Controllers;
use App\Exceptions\ApiException;
use App\Models\Activity\Coupon;
use App\Models\Answer;
use App\Models\Comment;
use App\Models\Notice;
use App\Models\Question;
use App\Models\Submission;
use App\Models\RecommendRead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/5/12 下午5:55
 * @email: wanghui@yonglibao.com
 */

class IndexController extends Controller {
    public function home(Request $request){
        $user = $request->user();
        $expire_at = '';

        $show_invitation_coupon = false;
        $show_ad = false;
        if($user){
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
            //新注册领取受邀注册红包
            //检查活动时间
            $ac_invitation_coupon_begin_time = Setting()->get('ac_invitation_coupon_begin_time');
            $ac_invitation_coupon_end_time = Setting()->get('ac_invitation_coupon_end_time');
            if ($user->rc_uid && strtotime($user->created_at) >= strtotime($ac_invitation_coupon_begin_time) && $ac_invitation_coupon_begin_time && $ac_invitation_coupon_end_time && $ac_invitation_coupon_begin_time <=date('Y-m-d H:i') && $ac_invitation_coupon_end_time > date('Y-m-d H:i')) {
                //用户是否已经领过红包
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_NEW_REGISTER_INVITATION)->first();
                if(!$coupon){
                    $show_invitation_coupon = true;
                }
            }
        }
        //轮播图
        $notices = Notice::orderBy('sort','desc')->take(4)->get()->toArray();

        $data = [
            'first_ask_ac' => ['show_first_ask_coupon'=>$show_ad,'coupon_expire_at'=>$expire_at],
            'invitation_coupon' => ['show'=>$show_invitation_coupon],
            'notices' => $notices
        ];

        return self::createJsonData(true,$data);
    }

    //精选推荐
    public function recommendRead(Request $request) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $reads = RecommendRead::where('audit_status',1)->orderBy('sort','desc')->simplePaginate($perPage);
        $result = $reads->toArray();
        foreach ($result['data'] as &$item) {
            switch ($item['read_type']) {
                case RecommendRead::READ_TYPE_SUBMISSION:
                    // '发现分享';
                    $object = Submission::find($item['source_id']);
                    $item['data']['comment_number'] = $object->comments_number;
                    $item['data']['support_number'] = $object->upvotes;
                    $item['data']['view_number'] = $object->views;
                    break;
                case RecommendRead::READ_TYPE_PAY_QUESTION:
                    // '专业问答';
                    $object = Question::find($item['source_id']);
                    $bestAnswer = $object->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get()->last();

                    $item['data']['price'] = $object->price;
                    $item['data']['average_rate'] = $bestAnswer->getFeedbackRate();
                    $item['data']['view_number'] = $bestAnswer->views;
                    $item['data']['support_number'] = $bestAnswer->supports;
                    break;
                case RecommendRead::READ_TYPE_FREE_QUESTION:
                    // '互动问答';
                    $object = Question::find($item['source_id']);
                    $item['data']['answer_number'] = $object->answers;
                    $item['data']['follower_number'] = $object->followers;
                    $item['data']['view_number'] = $object->views;
                    break;
                case RecommendRead::READ_TYPE_ACTIVITY:
                    // '活动';
                    break;
                case RecommendRead::READ_TYPE_PROJECT_OPPORTUNITY:
                    // '项目机遇';
                    break;
                case RecommendRead::READ_TYPE_FREE_QUESTION_ANSWER:
                    // '互动问答回复';
                    $object = Answer::find($item['source_id']);
                    $item['data']['comment_number'] = $object->comments;
                    $item['data']['support_number'] = $object->supports;
                    $item['data']['view_number'] = $object->views;
                    break;
            }
        }
        return self::createJsonData(true, $result);
    }

    public function myCommentList(Request $request){
        $uuid = $request->input('uuid');
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
        }
        $comments = $user->comments()->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = [];

        $origin_title = '';
        $comment_url = '';
        $type = 1;
        foreach ($comments as $comment) {
            switch ($comment->source_type) {
                case 'App\Models\Article':
                    $source = $comment->source;
                    $origin_title = '活动:'.$source->title;
                    $comment_url = '/EnrollmentStatus/'.$source->id;
                    break;
                case 'App\Models\Answer':
                    $source = $comment->source;
                    $question = $source->question;
                    if ($question->question_type == 1) {
                        $origin_title = '专业问答:'.$question->title;
                        $comment_url = '/askCommunity/major/'.$source->question_id;
                    } else {
                        $origin_title = '互动问答:'.$question->title;
                        $comment_url = '/askCommunity/interaction/'.$source->id;
                    }
                    break;
                case 'App\Models\Readhub\Comment':
                    continue;
                    $type = 2;
                    $readhub_comment = Comment::find($comment->source_id);
                    $submission = Submission::find($readhub_comment->submission_id);
                    if (!$submission) continue;
                    $origin_title = '文章:'.$submission->title;
                    $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                    break;
                case 'App\Models\Submission':
                    $type = 2;
                    $submission = Submission::find($comment->source_id);
                    if (!$submission) continue;
                    $origin_title = ($submission->type == 'link'?'文章:':'动态:').$submission->formatTitle();
                    $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                    break;
            }
            $return[] = [
                'id' => $comment->id,
                'type'    => $type,
                'content' => $comment->formatContent(),
                'origin_title' => $origin_title,
                'comment_url'  => $comment_url,
                'created_at' => date('Y/m/d H:i',strtotime($comment->created_at))
            ];
        }
        $list = $comments->toArray();
        $list['data'] = $return;
        return self::createJsonData(true,  $list);
    }

}