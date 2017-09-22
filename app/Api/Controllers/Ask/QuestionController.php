<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Question\AutoInvitation;
use App\Events\Frontend\System\Push;
use App\Exceptions\ApiException;
use App\Logic\PayQueryLogic;
use App\Logic\TagsLogic;
use App\Logic\TaskLogic;
use App\Logic\WechatNotice;
use App\Models\Activity\Coupon;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Credit;
use App\Models\Pay\Order;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionInvitation;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Payment\Client\Query;
use Payment\Config;

class QuestionController extends Controller
{

    /*问题创建校验*/
    protected $validateRules = [
        'order_id'    => 'required|integer',
        'description' => 'required|max:500',
        'price'=> 'required|between:1,388',
        'tags' => 'required'
    ];

    /**
     * 问题详情查看
     */
    public function info(Request $request)
    {

        $id = $request->input('id');
        $question = Question::find($id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $user = $request->user();

        $is_self = $user->id == $question->user_id;
        $is_answer_author = false;
        $is_pay_for_view = false;

        /*已解决问题*/
        $bestAnswer = [];
        if($question->status >= 6 ){
            $bestAnswer = $question->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get()->last();
        }

        /*设置通知为已读*/
        if($request->user()){
            $this->readNotifications($question->id,'question');
        }

        //已经回答的问题其他人都能看,没回答的问题只有邀请者才能看
        if ($question->status < 6) {
            //问题作者或邀请者才能看
            $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->first();
            if(empty($question_invitation) && !$is_self){
                throw new ApiException(ApiException::BAD_REQUEST);
            }
            //已经拒绝了
            if($question_invitation && $question_invitation->status == QuestionInvitation::STATUS_REJECTED){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_REJECTED);
            }
            //虽然邀请他回答了,但是已被其他人回答了
            if($request->user()->id != $question->user->id){
                $question_invitation_confirmed = QuestionInvitation::where('question_id','=',$question->id)->whereIn('status',[QuestionInvitation::STATUS_ANSWERED,QuestionInvitation::STATUS_CONFIRMED])->first();
                if($question_invitation_confirmed && $question_invitation_confirmed->user_id != $request->user()->id) {
                    throw new ApiException(ApiException::ASK_QUESTION_ALREADY_CONFIRMED);
                }
            }
        }


        $answers_data = [];
        $promise_answer_time = '';

        if($bestAnswer){
            //是否回答者
            if ($bestAnswer->user_id == $user->id) {
                $is_answer_author = true;
            }
            //是否已经付过围观费
            $payOrder = $bestAnswer->orders()->where('return_param','view_answer')->first();
            if ($payOrder) {
                $is_pay_for_view = true;
            }
            $answers_data[] = [
                'id' => $bestAnswer->id,
                'user_id' => $bestAnswer->user_id,
                'uuid' => $bestAnswer->user->uuid,
                'user_name' => $bestAnswer->user->name,
                'user_avatar_url' => $bestAnswer->user->avatar,
                'title' => $bestAnswer->user->title,
                'company' => $bestAnswer->user->company,
                'is_expert' => $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                'content' => ($is_self || $is_answer_author || $is_pay_for_view) ? $bestAnswer->content : '',
                'promise_time' => $bestAnswer->promise_time,
                'support_number' => $bestAnswer->supports,
                'view_number'    => $bestAnswer->views,
                'comment_number' => $bestAnswer->comments,
                'created_at' => (string)$bestAnswer->created_at
            ];
            $promise_answer_time = $bestAnswer->promise_time;
        }else {
            $promise_answer = $question->answers()->where('status',Answer::ANSWER_STATUS_PROMISE)->orderBy('id','desc')->get()->last();
            if ($promise_answer){
                $promise_answer_time = $promise_answer->promise_time;
            }
        }

        $question_data = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'question_type' => $question->question_type,
            'user_name' => $question->hide ? '匿名' : $question->user->name,
            'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl(),
            'user_description' => $question->hide ? '':$question->user->description,
            'description'  => $question->title,
            'tags' => $question->tags()->pluck('name'),
            'hide' => $question->hide,
            'price' => $question->price,
            'status' => $question->status,
            'status_description' => $question->statusHumanDescription($user->id),
            'promise_answer_time' => $promise_answer_time,
            'created_at' => (string)$question->created_at
        ];


        $timeline = $is_self ? $question->formatTimeline() : [];

        //feedback
        $feedback_data = [];
        if($is_self && $answers_data){
            $feedback = $bestAnswer->feedbacks()->orderBy('id','desc')->first();
            if(!empty($feedback)){
                $feedback_data = [
                    'answer_id' => $feedback->source_id,
                    'rate_star' => $feedback->star,
                    'description' => $feedback->content,
                    'create_time' => (string)$feedback->created_at
                ];
            }
        }


        return self::createJsonData(true,['question'=>$question_data,'answers'=>$answers_data,'timeline'=>$timeline,'feedback'=>$feedback_data]);

    }


    /**
     * 请求创建问题
     */
    public function request(Request $request)
    {
        $user = $request->user();

        $expert_uuid = $request->input('uuid');
        if($expert_uuid){
            $expert_user = User::where('uuid',$expert_uuid)->firstOrFail();
            $this->checkAnswerUser($user,$expert_user->id);
        }

        $this->checkUserInfoPercent($user);
        $tags = TagsLogic::loadTags(1,'');
        $show_free_ask = false;
        $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->where('coupon_status',Coupon::COUPON_STATUS_PENDING)->first();
        if($coupon && $coupon->expire_at > date('Y-m-d H:i:s')){
            $show_free_ask = true;
        }

        $tags['pay_items'] = [
            [
                'value'=>88,
                'text'=>'积极参与（ ¥ 88.00 ）',
                'default' => true
            ],
            [
                'value'=>188,
                'text'=>'鼎力支持（ ¥188.00 ）',
                'default' => false
            ],
            [
                'value'=>28,
                'text'=>'略表心意（ ¥ 28.00 ）',
                'default' => false
            ]
        ];
        if($show_free_ask){
            $tags['pay_items'][0]['default'] = false;
            $tags['pay_items'][] = [
                'value'=>1,
                'text'=>'首问优惠（￥1.00）',
                'default' => true
            ];
        }

        return self::createJsonData(true,$tags);
    }

    protected function checkAnswerUser($loginUser,$answer_user_id){
        if ($loginUser->id == $answer_user_id) {
            throw new ApiException(ApiException::ASK_CANNOT_INVITE_SELF);
        }

        $toUser = User::find(intval($answer_user_id));
        if (!$toUser) {
            throw new ApiException(ApiException::ASK_INVITE_USER_NOT_FOUND);
        }

        //是否设置了邀请者必须为专家
        if (Setting()->get('is_inviter_must_expert', 1) == 1) {
            if (($toUser->authentication && $toUser->authentication->status === 1)) {

            } else {
                //非专家
                throw new ApiException(ApiException::ASK_INVITE_USER_MUST_EXPERT);
            }
        }
    }


    /*创建提问*/
    public function store(Request $request)
    {
        $loginUser = $request->user();

        $this->validate($request,$this->validateRules);

        $this->checkUserInfoPercent($loginUser);

        $price = abs($request->input('price'));
        $tagString = trim($request->input('tags'));

        $category_id = 0;
        if($tagString){
            //目前只能添加一个标签
            $tags = array_unique(explode(",",$tagString));
            $tag = Tag::find($tags[0])->first();
            $category_id = $tag->category_id;
        }
        $data = [
            'user_id'      => $loginUser->id,
            'category_id'      => $category_id,
            'title'        => trim($request->input('description')),
            'price'        => $price,
            'hide'         => intval($request->input('hide')),
            'status'       => 1,
            'device'       => intval($request->input('device'))
        ];

        //查看支付订单是否成功
        $order = Order::find($request->input('order_id'));
        if(empty($order) && Setting()->get('need_pay_actual',1)){
            throw new ApiException(ApiException::ASK_PAYMENT_EXCEPTION);
        }

        $to_user_uuid = $request->input('answer_uuid');
        if($to_user_uuid) {
            $toUser = User::where('uuid',$to_user_uuid)->firstOrFail();
            $this->checkAnswerUser($loginUser,$toUser->id);
        }

        //如果订单存在且状态为处理中,有可能还未回调
        if($order && $order->status == Order::PAY_STATUS_PROCESS && Setting()->get('need_pay_actual',1)){
            if (PayQueryLogic::queryWechatPayOrder($order->id)){

            } else {
                $data['status'] = 0;
                $question = Question::create($data);
                \Log::error('提问支付订单还在处理中',[$question]);
                throw new ApiException(ApiException::ASK_PAYMENT_EXCEPTION);
            }
        }

        $question = Question::create($data);
        /*判断问题是否添加成功*/
        if($question){

            /*添加标签*/
            Tag::multiSaveByIds($tagString,$question);

            //订单和问题关联
            if($order){
                $question->orders()->attach($order->id);
            }

            //记录动态
            $this->doing($question->user_id,'question_submit',get_class($question),$question->id,$question->title,'');

            $waiting_second = rand(1,5);

            //因为微信支付要有30天的流水,所以指定用户id为3的每天做流水
            if(config('app.env') == 'production' && $loginUser->id == 3){
                $res_data = [
                    'id'=>$question->id,
                    'price'=> $price,
                    "tips_1"=> "平台已为您支付",
                    "tips_2"=> "受理反馈中",
                    "waiting_second" => $waiting_second,
                    'create_time'=>(string)$question->created_at
                ];
                return self::createJsonData(true,$res_data,ApiException::SUCCESS,'发起提问成功!');
            }

            if(!$to_user_uuid){
                $doing_obj = $this->doing(0,'question_process',get_class($question),$question->id,$question->title,'');
                $doing_obj->created_at = date('Y-m-d H:i:s',strtotime('+ '.$waiting_second.' seconds'));
                $doing_obj->save();
            }

            /*用户提问数+1*/
            $loginUser->userData()->increment('questions');
            UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'questions');
            //首次提问
            if($loginUser->userData->questions == 1){
                $this->credit($request->user()->id,Credit::KEY_FIRST_ASK,$question->id,$question->title);
                TaskLogic::finishTask('newbie_ask',0,'newbie_ask',[$request->user()->id]);
            } else {
                $this->credit($request->user()->id,Credit::KEY_ASK,$question->id,$question->title);
            }
            //1元优惠使用红包
            if($price == 1){
                $coupon = Coupon::where('user_id',$loginUser->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->first();
                if($coupon && $coupon->used_object_id){
                    $coupon->coupon_status = Coupon::COUPON_STATUS_USED;
                    $coupon->used_at = date('Y-m-d H:i:s');
                    $coupon->save();
                }
            }

            $message = '发起提问成功!';

            $this->counter( 'question_num_'. $question->user_id , 1 , 3600 );

            if($to_user_uuid){
                $invitation = QuestionInvitation::firstOrCreate(['user_id'=>$toUser->id,'from_user_id'=>$question->user_id,'question_id'=>$question->id],[
                    'from_user_id'=> $question->user_id,
                    'question_id'=> $question->id,
                    'user_id'=> $toUser->id,
                    'send_to'=> $toUser->email
                ]);

                //已邀请
                $question->invitedAnswer();
                //记录动态
                $this->doing($toUser->id,'question_invite_answer_confirming',get_class($question),$question->id,$question->title,'',0,$question->user_id);
                //记录任务
                $this->task($toUser->id,get_class($question),$question->id,Task::ACTION_TYPE_ANSWER);
                //通知
                $toUser->notify(new NewQuestionInvitation($toUser->id,$question));
            }else{
                //非定向邀请的自动匹配一次
                event(new AutoInvitation($question));
            }

            $res_data = [
                'id'=>$question->id,
                'price'=> $price,
                "tips_1"=> "平台已为您支付",
                "tips_2"=> "受理反馈中",
                "waiting_second" => $waiting_second,
                'create_time'=>(string)$question->created_at
            ];
            return self::createJsonData(true,$res_data,ApiException::SUCCESS,$message);

        }
        throw new ApiException(ApiException::ERROR);

    }


    //拒绝回答
    public function rejectAnswer(Request $request){
        $loginUser = $request->user();

        $validateRules = [
            'question_id' => 'required',
            'tags' => 'required'
        ];
        $this->validate($request,$validateRules);

        $question = Question::find($request->input('question_id'));
        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->first();
        if(empty($question_invitation)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        if($question_invitation->status != 0){
            throw new ApiException(ApiException::ASK_QUESTION_ALREADY_CONFIRMED);
        }

        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $request->input('question_id'),
            'content'  => $request->input('description','')?:'拒绝回答',
            'status'   => Answer::ANSWER_STATUS_REJECT,
        ];

        $answer = Answer::create($data);

        //是否有其它待回答
        $otherAnswers = Answer::where('question_id',$question->id)->where('status','!=',2)->first();
        $other_question_invitations = QuestionInvitation::where('question_id','=',$question->id)->where('status',0)->first();
        if(!$otherAnswers && !$other_question_invitations){
            //问题已拒绝
            $question->rejectAnswer();
        }
        $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[$loginUser->id]);

        /*添加标签*/
        $tagString = trim($request->input('tags'));
        Tag::multiSaveByIds($tagString,$answer);
        /*记录动态*/
        $this->doing($answer->user_id,'question_answer_rejected',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id);
        /*修改问题邀请表的回答状态*/
        QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>QuestionInvitation::STATUS_REJECTED]);

        return self::createJsonData(true,['question_id'=>$data['question_id'],'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
    }

    //我的提问列表
    public function myList(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $type = $request->input('type',0);

        $query = $request->user()->questions();
        switch($type){
            case 1:
                //未完成
                $query = $query->where('status','<=',6);
                break;
            case 2:
                //已完成
                $query = $query->where('status',7);
                break;
        }

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }
        $questions = $query->orderBy('id','DESC')->paginate(10);

        $list = [];
        foreach($questions as $question){
            /*已解决问题*/
            $bestAnswer = [];
            if($question->status >= 6 ){
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
            }
            $list[] = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'user_id' => $question->user_id,
                'user_name' => $question->user->name,
                'user_avatar_url' => $question->user->getAvatarUrl(),
                'description'  => $question->title,
                'tags' => $question->tags()->pluck('name'),
                'hide' => $question->hide,
                'price' => $question->price,
                'status' => $question->status,
                'status_description' => $question->statusHumanDescription($question->user_id),
                'created_at' => (string)$question->created_at,
                'answer_user_id' => $bestAnswer ? $bestAnswer->user->id : '',
                'answer_username' => $bestAnswer ? $bestAnswer->user->name : '',
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->avatar : '',
                'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : ''
            ];
        }
        return self::createJsonData(true,$list);
    }


    //专业问答-推荐问答列表
    public function majorList(Request $request) {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $tag_id = $request->input('tag_id',0);

        $query = Question::where('questions.is_recommend',1);
        if($top_id){
            $query = $query->where('questions.id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('questions.id','<',$bottom_id);
        }

        if ($tag_id) {
            $query = $query->leftJoin('taggables','questions.id','=','taggables.taggable_id')->where('taggables.taggable_type','App\Models\Question')->where('taggables.taggable_id',$tag_id);
        }

        $questions = $query->orderBy('questions.id','desc')->paginate(10);
        $list = [];
        foreach($questions as $question){
            /*已解决问题*/
            $bestAnswer = [];
            if($question->status >= 6 ){
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
            }
            $list[] = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'user_id' => $question->user_id,
                'description'  => $question->title,
                'tags' => $question->tags()->pluck('name'),
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
        return self::createJsonData(true,$list);
    }

    //专业问答-热门问答
    public function majorHot(Request $request) {
        $questions = Question::where('is_hot',1)->orderBy('id','desc')->get()->take(2);
        $list = [];
        foreach($questions as $question){
            /*已解决问题*/
            $bestAnswer = [];
            if($question->status >= 6 ){
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
            }
            $list[] = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'user_id' => $question->user_id,
                'description'  => $question->title,
                'tags' => $question->tags()->pluck('name'),
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
        return self::createJsonData(true,$list);
    }



    protected function checkUserInfoPercent($user){
        //字段完成度为90%才能创建问题
        $percent = $user->getInfoCompletePercent();
        $valid_percent = config('inwehub.user_info_valid_percent',90);
        if($percent < $valid_percent){
            throw new ApiException(ApiException::ASK_NEED_USER_INFORMATION);
        }
    }

}
