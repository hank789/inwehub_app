<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Question\AutoInvitation;
use App\Exceptions\ApiException;
use App\Logic\PayQueryLogic;
use App\Logic\TagsLogic;
use App\Logic\TaskLogic;
use App\Models\Activity\Coupon;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Pay\Order;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Support;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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

        //已经回答的问题其他人都能看,没回答的问题只有邀请者才能看(付费专业问答)
        if ($question->question_type == 1 && $question->status < 6) {
            //问题作者或邀请者才能看
            $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$user->id)->first();
            if(empty($question_invitation) && !$is_self){
                throw new ApiException(ApiException::BAD_REQUEST);
            }
            //已经拒绝了
            if($question_invitation && $question_invitation->status == QuestionInvitation::STATUS_REJECTED){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_REJECTED);
            }
            //虽然邀请他回答了,但是已被其他人回答了
            if($user->id != $question->user->id){
                $question_invitation_confirmed = QuestionInvitation::where('question_id','=',$question->id)->whereIn('status',[QuestionInvitation::STATUS_ANSWERED,QuestionInvitation::STATUS_CONFIRMED])->first();
                if($question_invitation_confirmed && $question_invitation_confirmed->user_id != $user->id) {
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
            $payOrder = $bestAnswer->orders()->where('user_id',$user->id)->where('return_param','view_answer')->first();
            if ($payOrder) {
                $is_pay_for_view = true;
            }
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($bestAnswer->user))->where('source_id','=',$bestAnswer->user_id)->first();

            $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($bestAnswer))->where('supportable_id','=',$bestAnswer->id)->first();
            //回答查看数增加
            //if ($is_self || $is_answer_author || $is_pay_for_view) $bestAnswer->increment('views');
            $bestAnswer->increment('views');
            $support_uids = Support::where('supportable_type','=',get_class($bestAnswer))->where('supportable_id','=',$bestAnswer->id)->take(20)->pluck('user_id');
            $supporters = [];
            if ($support_uids) {
                $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
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
                'is_followed' => $attention?1:0,
                'is_supported' => $support?1:0,
                'support_number' => $bestAnswer->supports,
                'view_number'    => $bestAnswer->views,
                'comment_number' => $bestAnswer->comments,
                'average_rate'   => $bestAnswer->getFeedbackRate(),
                'created_at' => (string)$bestAnswer->created_at,
                'supporter_list' => $supporters
            ];
            $promise_answer_time = $bestAnswer->promise_time;
        }else {
            $promise_answer = $question->answers()->where('status',Answer::ANSWER_STATUS_PROMISE)->orderBy('id','desc')->get()->last();
            if ($promise_answer){
                $promise_answer_time = $promise_answer->promise_time;
            }
        }

        $attention_question_user = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question->user))->where('source_id','=',$question->user_id)->first();

        $question_data = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'uuid'    => $question->hide ? '':$question->user->uuid,
            'question_type' => $question->question_type,
            'user_name' => $question->hide ? '匿名' : $question->user->name,
            'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl(),
            'title' => $question->hide ? '保密' : $question->user->title,
            'company' => $question->hide ? '保密' : $question->user->company,
            'is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
            'is_followed' => $question->hide ? 0 : ($attention_question_user?1:0),
            'user_description' => $question->hide ? '':$question->user->description,
            'description'  => $question->title,
            'tags' => $question->tags()->pluck('name'),
            'hide' => $question->hide,
            'price' => $question->price,
            'status' => $question->status,
            'status_description' => $question->statusHumanDescription($user->id),
            'promise_answer_time' => $promise_answer_time,
            'answer_num' => $question->answers,
            'follow_num' => $question->followers,
            'created_at' => (string)$question->created_at
        ];


        $timeline = $is_self && $question->question_type == 1 ? $question->formatTimeline() : [];

        //feedback
        $feedback_data = [];
        if($answers_data){
            $feedback = $bestAnswer->feedbacks()->where('user_id',$user->id)->orderBy('id','desc')->first();
            if(!empty($feedback)){
                $feedback_data = [
                    'answer_id' => $feedback->source_id,
                    'rate_star' => $feedback->star,
                    'description' => $feedback->content,
                    'create_time' => (string)$feedback->created_at
                ];
            }
        }
        $is_followed_question = 0;
        $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
        if ($attention_question) {
            $is_followed_question = 1;
        }

        $my_answer_id = 0;
        if ($question->question_type != 1) {
            $myAnswer = Answer::where('question_id',$id)->where('user_id',$user->id)->first();
            if ($myAnswer) $my_answer_id = $myAnswer->id;
        }

        return self::createJsonData(true,[
            'is_followed_question'=>$is_followed_question,
            'my_answer_id' => $my_answer_id,
            'question'=>$question_data,
            'answers'=>$answers_data,
            'timeline'=>$timeline,
            'feedback'=>$feedback_data]);

    }


    //相关问题
    public function relatedQuestion(Request $request){
        $validateRules = [
            'id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $question_id = $request->input('id');
        $question = Question::find($question_id);
        $relatedQuestions = Question::correlations($question->tags()->pluck('tag_id'));
        if (!$relatedQuestions) {
            $relatedQuestions = Question::recent();
        }
        $list = [];
        $count = 0;
        foreach ($relatedQuestions as $relatedQuestion) {
            if ($count >= 1) break;
            $bestAnswer = $relatedQuestion->answers()->orderBy('id','desc')->get()->last();
            if (!$bestAnswer) continue;
            $list[] = [
                'id' => $relatedQuestion->id,
                'user_id' => $bestAnswer->user_id,
                'user_name' => $bestAnswer->user->name,
                'user_avatar_url' => $bestAnswer->user->avatar,
                'is_expert' => $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                'title' => $relatedQuestion->title
            ];
            $count++;
        }
        return self::createJsonData(true,$list);
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
                'value'=>60,
                'text'=>'积极参与（ ¥ 60.00 ）',
                'default' => true
            ],
            [
                'value'=>88,
                'text'=>'鼎力支持（ ¥88.00 ）',
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
        $tagString = $request->input('tags');

        $category_id = 20;
        $data = [
            'user_id'      => $loginUser->id,
            'category_id'      => $category_id,
            'title'        => trim($request->input('description')),
            'question_type' => $request->input('question_type',1),
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
            $doing_prefix = '';
            if ($question->question_type == 2) {
                $doing_prefix = 'free_';
            }

            //记录动态
            $this->doing($question->user_id,$doing_prefix.'question_submit',get_class($question),$question->id,$question->title,'');

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

            if($question->question_type == 1 && !$to_user_uuid){
                $doing_obj = $this->doing(0,$doing_prefix.'question_process',get_class($question),$question->id,$question->title,'');
                $doing_obj->created_at = date('Y-m-d H:i:s',strtotime('+ '.$waiting_second.' seconds'));
                $doing_obj->save();
            }

            /*用户提问数+1*/
            $loginUser->userData()->increment('questions');
            UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'questions');
            //首次提问
            if($loginUser->userData->questions == 1){
                if ($question->question_type == 1) {
                    $credit_key = Credit::KEY_FIRST_ASK;
                } else {
                    $credit_key = Credit::KEY_FIRST_COMMUNITY_ASK;
                }
                TaskLogic::finishTask('newbie_ask',0,'newbie_ask',[$request->user()->id]);
            } else {
                if ($question->question_type == 1) {
                    $credit_key = Credit::KEY_ASK;
                } else {
                    $credit_key = Credit::KEY_COMMUNITY_ASK;
                }
            }
            $this->credit($request->user()->id,$credit_key,$question->id,$question->title);

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
                $this->doing($toUser->id,$doing_prefix.'question_invite_answer_confirming',get_class($question),$question->id,$question->title,'',0,$question->user_id);
                //记录任务
                $this->task($toUser->id,get_class($question),$question->id,Task::ACTION_TYPE_ANSWER);
                //通知
                $toUser->notify(new NewQuestionInvitation($toUser->id,$question));
            }elseif ($question->question_type == 1){
                //专业问答非定向邀请的自动匹配一次
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


    //邀请回答
    public function inviteAnswer(Request $request){
        $validateRules = [
            'question_id'    => 'required|integer',
            'user_id' => 'required|integer',
        ];
        $this->validate($request,$validateRules);

        $loginUser = $request->user();
        $to_user_id = $request->input('user_id');
        $question_id = $request->input('question_id');

        if($loginUser->id == $to_user_id){
            return self::createJsonData(false,[],ApiException::BAD_REQUEST,'不用邀请自己，您可以直接回答 ：）');
        }

        $question = Question::find($question_id);
        if(!$question){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        if($to_user_id == $question->user_id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }


        $toUser = User::find(intval($to_user_id));
        if(!$toUser){
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        /*是否已邀请，不能重复邀请*/
        if($question->isInvited($toUser->id,$loginUser->id)){
            return self::createJsonData(false,[],ApiException::BAD_REQUEST,'该用户已被邀请，不能重复邀请');
        }

        $invitation = QuestionInvitation::firstOrCreate(['user_id'=>$toUser->id,'from_user_id'=>$loginUser->id,'question_id'=>$question->id],[
            'from_user_id'=> $loginUser->id,
            'question_id'=> $question->id,
            'user_id'=> $toUser->id,
            'send_to'=> $toUser->email
        ]);

        //记录动态
        //记录任务
        //$this->task($to_user_id,get_class($invitation),$invitation->id,Task::ACTION_TYPE_INVITE_ANSWER);

        $toUser->notify(new NewQuestionInvitation($toUser->id, $question, $loginUser->id));

        return self::createJsonData(true);
    }


    //邀请回答者列表
    public function inviterList(Request $request){
        $validateRules = [
            'question_id'    => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $question = Question::find($request->input('question_id'));
        if(!$question){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $query = $request->user()->attentions()->where('source_type','=','App\Models\User');
        $attentions = $query->orderBy('attentions.created_at','desc')->get();
        $data = [];
        foreach($attentions as $attention){
            if ($attention->source_id == $question->user_id) continue;
            $info = User::find($attention->source_id);
            $item = [];
            $item['id'] = $info->id;
            $item['name'] = $info->name;
            $item['avatar_url'] = $info->getAvatarUrl();
            $item['is_expert'] = ($info->authentication && $info->authentication->status === 1) ? 1 : 0;
            $item['description'] = $info->description;
            $item['is_invited'] = $question->isInvited($info->id,$request->user()->id);
            $data[] = $item;
        }
        return self::createJsonData(true,$data);


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
        $uuid = $request->input('uuid');
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
            $query = $user->questions()->where('hide',0);
        } else {
            $query = $request->user()->questions();
        }
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
        $questions = $query->orderBy('id','DESC')->paginate(Config::get('api_data_page_size'));

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
                'description'  => $question->getFormatTitle(),
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

        $query = Question::where('questions.is_recommend',1)->where('questions.question_type',1);
        if($top_id){
            $query = $query->where('questions.id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('questions.id','<',$bottom_id);
        }

        if ($tag_id) {
            $query = $query->leftJoin('taggables','questions.id','=','taggables.taggable_id')->where('taggables.taggable_type','App\Models\Question')->where('taggables.taggable_id',$tag_id);
        }

        $questions = $query->orderBy('questions.id','desc')->paginate(Config::get('api_data_page_size'));
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
                'answer_user_is_expert' => $bestAnswer && $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->avatar : '',
                'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : ''
            ];
        }
        return self::createJsonData(true,$list);
    }

    //互动问答-问答列表
    public function commonList(Request $request) {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $tag_id = $request->input('tag_id',0);
        $user = $request->user();

        $query = Question::where('questions.question_type',2);
        if($top_id){
            $query = $query->where('questions.id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('questions.id','<',$bottom_id);
        }

        if ($tag_id) {
            $query = $query->leftJoin('taggables','questions.id','=','taggables.taggable_id')->where('taggables.taggable_type','App\Models\Question')->where('taggables.taggable_id',$tag_id);
        }

        $questions = $query->orderBy('questions.updated_at','desc')->paginate(Config::get('api_data_page_size'));
        $list = [];
        foreach($questions as $question){
            $is_followed_question = 0;
            $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
            if ($attention_question) {
                $is_followed_question = 1;
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
                'question_username' => $question->hide ? '匿名' : $question->user->name,
                'question_user_is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
                'question_user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                'answer_num' => $question->answers,
                'follow_num' => $question->followers,
                'is_followed_question' => $is_followed_question
            ];
        }
        return self::createJsonData(true,$list);
    }

    //专业问答-热门问答
    public function majorHot(Request $request) {
        $questions = Question::where('is_hot',1)->orderBy('views','desc')->get()->take(2);
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
                'answer_user_is_expert' => $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->avatar : '',
                'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : ''
            ];
        }
        return self::createJsonData(true,$list);
    }

    //问题回答列表
    public function answerList(Request $request){
        $id = $request->input('question_id');
        $question = Question::find($id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $user = $request->user();
        $answers = $question->answers()->whereNull('adopted_at')->orderBy('supports','DESC')->orderBy('updated_at','desc')->simplePaginate(Config::get('api_data_page_size'));
        $return = $answers->toArray();
        $return['data'] = [];
        foreach ($answers as $answer) {
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($answer->user))->where('source_id','=',$answer->user_id)->first();

            $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->first();

            $return['data'][] = [
                'id' => $answer->id,
                'user_id' => $answer->user_id,
                'uuid' => $answer->user->uuid,
                'user_name' => $answer->user->name,
                'user_avatar_url' => $answer->user->avatar,
                'title' => $answer->user->title,
                'company' => $answer->user->company,
                'is_expert' => $answer->user->userData->authentication_status == 1 ? 1 : 0,
                'content' => $answer->getContentText(),
                'promise_time' => $answer->promise_time,
                'is_followed' => $attention?1:0,
                'is_supported' => $support?1:0,
                'support_number' => $answer->supports,
                'view_number'    => $answer->views,
                'comment_number' => $answer->comments,
                'created_at' => (string)$answer->created_at
            ];
        }
        return self::createJsonData(true,$return);
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
