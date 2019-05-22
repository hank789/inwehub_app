<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\Question\ConfirmOvertime;
use App\Jobs\QuestionRefund;
use App\Logic\PayQueryLogic;
use App\Logic\QuestionLogic;
use App\Logic\TaskLogic;
use App\Models\Activity\Coupon;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\DownVote;
use App\Models\Pay\Order;
use App\Models\Pay\UserMoney;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Support;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionInvitation;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

class QuestionController extends Controller
{

    /*问题创建校验*/
    protected $validateRules = [
        'order_id'    => 'required|integer',
        'description' => 'required|max:500',
        'price'=> 'required|integer|between:5,20000',
    ];

    /**
     * 问题详情查看
     */
    public function info(Request $request,JWTAuth $JWTAuth)
    {

        $id = $request->input('id');
        $question = Question::find($id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }

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
            $payOrder = $bestAnswer->orders()->where('user_id',$user->id)->where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->first();
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
                foreach ($support_uids as $support_uid) {
                    $supporter = User::find($support_uid);
                    $supporters[] = [
                      'name' => $supporter->name,
                      'uuid' => $supporter->uuid
                    ];
                }
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
                'created_at' => $bestAnswer->created_at->diffForHumans(),
                'created_time' => $bestAnswer->created_at,
                'supporter_list' => $supporters
            ];
            $promise_answer_time = $bestAnswer->promise_time;
        }else {
            $promise_answer = $question->answers()->where('status',Answer::ANSWER_STATUS_PROMISE)->orderBy('id','desc')->get()->last();
            if ($promise_answer){
                $promise_answer_time = $promise_answer->promise_time;
            }
        }
        $question->increment('views');


        $attention_question_user = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question->user))->where('source_id','=',$question->user_id)->first();
        $qData = $question->data;
        if (!isset($qData['img'])) {
            $qData['img'] = [];
        }
        $question_data = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'uuid'    => $question->user->uuid,
            'question_type' => $question->question_type,
            'user_name' => $question->hide ? '匿名' : $question->user->name,
            'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl(),
            'title' => $question->hide ? '保密' : $question->user->title,
            'company' => $question->hide ? '保密' : $question->user->company,
            'is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
            'is_followed' => $question->hide ? 0 : ($attention_question_user?1:0),
            'user_description' => $question->hide ? '':$question->user->description,
            'data' => $qData,
            'description'  => $question->title,
            'tags' => $question->tags()->wherePivot('is_display',1)->get()->toArray(),
            'hide' => $question->hide,
            'price' => $question->price,
            'status' => $question->status,
            'status_description' => $question->statusFormatDescription($user->id),
            'status_short_tip' => $question->statusShortTip($user->id),
            'promise_answer_time' => $promise_answer_time,
            'question_answer_num' => $question->answers,
            'question_follow_num' => $question->followers,
            'views' => $question->views,
            'created_at' => $question->created_at->diffForHumans(),
            'created_time' => $question->created_at
        ];


        $timeline = [];

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
        $this->doing($user,$question->question_type == 1 ? Doing::ACTION_VIEW_PAY_QUESTION:Doing::ACTION_VIEW_FREE_QUESTION,get_class($question),$question->id,$question->title,'',0,0,
            '',config('app.mobile_url').'#/ask/offer/answers/'.$question->id);
        $this->logUserViewTags($user->id,$question->tags()->get());
        QuestionLogic::calculationQuestionRate($question->id);

        //seo信息
        $keywords = array_unique(explode(',',$question->data['keywords']??''));
        $seo = [
            'title' => strip_tags($question->title),
            'description' => strip_tags($question->title),
            'keywords' => implode(',',array_slice($keywords,0,5)),
            'published_time' => (new Carbon($question->created_at))->toAtomString()
        ];
        $related_products = $question->getRelatedProducts();
        return self::createJsonData(true,[
            'is_followed_question'=>$is_followed_question,
            'my_answer_id' => $my_answer_id,
            'is_login' => $user->id ? true:false,
            'question'=>$question_data,
            'answers'=>$answers_data,
            'timeline'=>$timeline,
            'seo' => $seo,
            'related_products' => $related_products,
            'feedback'=>$feedback_data]);

    }


    //相关问题
    public function relatedQuestion(Request $request){
        $validateRules = [
            'id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $question_id = $request->input('id');
        $limit = $request->input('limit',2);
        $currentQuestion = Question::find($question_id);
        if (empty($currentQuestion)) return self::createJsonData(true);
        $relatedQuestions = Question::correlationsPage($currentQuestion->tags()->pluck('tag_id'),10,'',[],[$question_id]);
        if ($relatedQuestions->count() <= 0) {
            $relatedQuestions = Question::recent();
        }
        $list = [];
        $count = 0;
        foreach ($relatedQuestions as $question) {
            if ($question->id == $question_id) continue;
            $item = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'description'  => $question->title,
                'price'      => $question->price,
                'tags' => $question->tags()->wherePivot('is_display',1)->get()->toArray(),
                'status' => $question->status
            ];
            if($question->question_type == 1){
                $item['comment_number'] = 0;
                $item['average_rate'] = 0;
                $item['support_number'] = 0;
                $item['status_description'] = $question->price.'元';
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
                if ($bestAnswer) {
                    $item['comment_number'] = $bestAnswer->comments;
                    $item['average_rate'] = $bestAnswer->getFeedbackRate();
                    $item['support_number'] = $bestAnswer->supports;
                    $item['feedback_rate'] = $bestAnswer->getFeedbackAverage();
                    $item['support_rate'] = $bestAnswer->getSupportRate();
                }
            } else {
                $item['answer_number'] = $question->answers;
                $item['follow_number'] = $question->followers;
                $item['status_description'] = $question->price?($question->price.'元悬赏'.($question->status != 8 ? '中':'')):'';
            }
            $list[] = $item;
            $count++;
            if ($count >= $limit) break;
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
            $expert_user = User::where('uuid',$expert_uuid)->first();
            if ($expert_user) {
                $this->checkAnswerUser($user,$expert_user->id);
            }
        }

        $this->checkUserInfoPercent($user);
        $tags = [];
        $show_free_ask = false;
        $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->where('coupon_status',Coupon::COUPON_STATUS_PENDING)->first();
        if($coupon && $coupon->expire_at > date('Y-m-d H:i:s')){
            $show_free_ask = true;
        }
        $tags['total_money'] = 0;
        $tags['must_apple_pay'] = false;

        $user_money = UserMoney::find($user->id);
        if($user_money && !in_array($user->id,[504])){
            $tags['total_money'] = $user_money->total_money;
        }
        if (in_array($user->id,[504]) || $expert_uuid) {
            $tags['must_apple_pay'] = true;
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
        $tags['title'] = '悬赏提问';
        $tags['help_tips'] = '请输入问题描述';
        if ($expert_uuid && $expert_user) {
            //定向提问
            $tags['title'] = '向'.$expert_user->name.'付费提问';
            $tags['help_tips'] = '1.请精准输入问题详情，并等待专家回答，若超过48小时未被回答，费用自动退回\n\n2.答案每被付费围观一次，你和回答者可从中获取分成：\n28元提问，按3:7分成\n60元提问，按5:5分成\n88元提问，按7:3分成';
        } else {
            $tags['pay_items'][0]['value'] = 10;
        }


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
        $newTagString = $request->input('new_tags');
        if ($newTagString) {
            if (is_array($newTagString)) {
                foreach ($newTagString as $s) {
                    if (strlen($s) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
                }
            } else {
                if (strlen($newTagString) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
            }
        }
        if (empty($tagString) && empty($newTagString)) {
            throw new ApiException(ApiException::ASK_TAGS_REQUIRED);
        }

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

        $category_id = 20;
        $data = [
            'user_id'      => $loginUser->id,
            'category_id'      => $category_id,
            'title'        => formatContentUrls(trim($request->input('description'))),
            'question_type' => $to_user_uuid?1:2,
            'price'        => $price,
            'hide'         => intval($request->input('hide')),
            'status'       => 1,
            'device'       => intval($request->input('device')),
            'rate'          => firstRate()
        ];

        $data['data'] = $this->uploadImgs($request->input('photos'),'questions');

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
            if ($newTagString) {
                Tag::multiAddByName($newTagString,$question);
            }

            //订单和问题关联
            if($order){
                $question->orders()->attach($order->id);
                //是否存在余额支付订单
                $order1 = Order::where('order_no',$order->order_no.'W')->first();
                if ($order1) {
                    $question->orders()->attach($order1->id);
                }
            }
            $doing_prefix = '';
            if ($question->question_type == 2) {
                $doing_prefix = 'free_';
            }

            //记录动态
            $this->doing($question->user,$doing_prefix.'question_submit',get_class($question),$question->id,$question->title,'');

            $waiting_second = rand(1,5);

            //因为微信支付要有30天的流水,所以指定用户id为3的每天做流水
            /*if(config('app.env') == 'production' && $loginUser->id == 3){
                $res_data = [
                    'id'=>$question->id,
                    'price'=> $price,
                    "tips_1"=> "平台已为您支付",
                    "tips_2"=> "受理反馈中",
                    "waiting_second" => $waiting_second,
                    'create_time'=>(string)$question->created_at
                ];
                return self::createJsonData(true,$res_data,ApiException::SUCCESS,'发起提问成功!');
            }*/

            if($question->question_type == 1 && !$to_user_uuid){
                $doing_obj = TaskLogic::doing(0,$doing_prefix.'question_process',get_class($question),$question->id,$question->title,'');
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
                    if ($question->hide == 1) {
                        $credit_key = Credit::KEY_FIRST_COMMUNITY_HIDE_ASK;
                    }
                }
                TaskLogic::finishTask('newbie_ask',0,'newbie_ask',[$request->user()->id]);
            } else {
                if ($question->question_type == 1) {
                    $credit_key = Credit::KEY_ASK;
                } else {
                    $credit_key = Credit::KEY_COMMUNITY_ASK;
                    if ($question->hide == 1) {
                        $credit_key = Credit::KEY_COMMUNITY_HIDE_ASK;
                    }
                }
            }
            //匿名互动提问的不加分
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
                $this->doing($toUser,$doing_prefix.'question_invite_answer_confirming',get_class($question),$question->id,$question->title,'',0,$question->user_id);
                //记录任务
                $this->task($toUser->id,get_class($question),$question->id,Task::ACTION_TYPE_ANSWER);
                //通知
                $toUser->notify(new NewQuestionInvitation($toUser->id,$question,$loginUser->id,$invitation->id));
                //延时处理是否需要告警
                dispatch((new ConfirmOvertime($question->id,$invitation->id))->delay(Carbon::now()->addMinutes(Setting()->get('alert_minute_expert_unconfirm_question',60))));
            }
            //48小时候若未有回答则退款
            $this->dispatch((new QuestionRefund($question->id))->delay(Carbon::now()->addHours(48)));
            //悬赏问答尝试邀请用户回答问题
            if ($question->question_type == 2) {
                //event(new AutoInvitation($question));
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
            'user_id' => 'required',
        ];
        $this->validate($request,$validateRules);

        $loginUser = $request->user();
        $to_user_ids = $request->input('user_id');
        $question_id = $request->input('question_id');

        $question = Question::find($question_id);
        if(!$question){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        if (!is_array($to_user_ids)) {
            $to_user_ids = [$to_user_ids];
        }
        $fields = [];
        $fields[] = [
            'title' => '问题标题',
            'value' => $question->title
        ];
        $fields[] = [
            'title' => '标签',
            'value' => implode(',',$question->tags()->pluck('name')->toArray())
        ];
        $fields[] = [
            'title' => '类型',
            'value' => '问答'
        ];
        $url = route('ask.question.detail',['id'=>$question->id]);
        $fields[] = [
            'title' => '地址',
            'value' => $url
        ];
        foreach ($to_user_ids as $to_user_id) {
            if($loginUser->id == $to_user_id){
                continue;
            }

            if($to_user_id == $question->user_id){
                continue;
            }

            $toUser = User::find(intval($to_user_id));
            if(!$toUser){
                continue;
            }

            /*是否已邀请，不能重复邀请*/
            if($question->isInvited($toUser->id,$loginUser->id)){
                continue;
            }
            $fields[] = [
                'title' => '邀请回答者',
                'value' => $toUser->id.'['.$toUser->name.']'
            ];

            $invitation = QuestionInvitation::firstOrCreate(['user_id'=>$toUser->id,'from_user_id'=>$loginUser->id,'question_id'=>$question->id],[
                'from_user_id'=> $loginUser->id,
                'question_id'=> $question->id,
                'user_id'=> $toUser->id,
                'send_to'=> $toUser->email
            ]);

            $toUser->notify(new NewQuestionInvitation($toUser->id, $question, $loginUser->id,$invitation->id,false));
            $this->credit($loginUser->id,Credit::KEY_COMMUNITY_ANSWER_INVITED,$invitation->id,$toUser->id,false);
        }
        event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']批量邀请回答问题',$fields));

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
            if (!$info) continue;
            $item = [];
            $item['id'] = $info->id;
            $item['name'] = $info->name;
            $item['avatar_url'] = $info->getAvatarUrl();
            $item['is_expert'] = ($info->authentication && $info->authentication->status === 1) ? 1 : 0;
            $item['description'] = $info->description;
            $item['is_invited'] = $question->isInvited($info->id,$request->user()->id);
            $item['is_answered'] = $question->answers()->where('user_id',$info->id)->exists();
            $data[] = $item;
        }
        return self::createJsonData(true,$data);
    }


    //一键邀请回答
    public function recommendInviterList(Request $request){
        $validateRules = [
            'question_id'    => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $question = Question::find($request->input('question_id'));
        if(!$question){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $page = $request->input('page',1);
        $page = $page%3;
        if ($page == 0) {
            $page = 3;
        }
        $tags = $question->tags()->pluck('tag_id')->toArray();
        //已经邀请过的用户
        $invitedUsers = $question->invitations()->where("from_user_id","=",$request->user()->id)->pluck('user_id')->toArray();
        $invitedUsers[] = $question->user_id;
        $invitedUsers[] = $request->user()->id;
        //已经回答过的用户
        $answeredUids = $question->answers()->pluck('user_id')->toArray();
        $invitedUsers = array_unique(array_merge($invitedUsers,getSystemUids(),$answeredUids));
        $banUsers = User::where('status',-1)->get()->pluck('id')->toArray();
        if ($banUsers) {
            $invitedUsers = array_unique(array_merge($invitedUsers,$banUsers));
        }
        $query = UserTag::select('user_id');
        $query1 = UserTag::select('user_id');
        if ($invitedUsers) {
            $query = $query->whereNotIn('user_id',$invitedUsers);
            $query1 = $query1->whereNotIn('user_id',$invitedUsers);
        }
        if ($tags) {
            $query = $query->whereIn('tag_id',$tags)->orderBy('skills','desc')->orderBy('answers','desc')->distinct();
            if ($query->count() <= 0) {
                $query1 = $query1->orderBy(DB::raw('RAND()'))->distinct();
                $userTags = $query1->take(15)->get();
            } else {
                $query1 = $query1->orderBy(DB::raw('RAND()'))->distinct();
                $query = $query->union($query1);
                $userTags = $query->simplePaginate(15,'*','page',$page);
            }
        } else {
            $query = $query->orderBy(DB::raw('RAND()'))->distinct();
            $userTags = $query->take(15)->get();
        }

        $data = [];
        foreach($userTags as $userTag){
            $info = User::find($userTag->user_id);
            $tag = $info->userTag()->whereIn('tag_id',$tags)->orderBy('skills','desc')->first();
            $item = [];
            if ($tag) {
                if ($tag->skills > 0) {
                    $skillTags = Tag::select('name')->whereIn('id',$info->userSkillTag()->pluck('tag_id'))->distinct()->pluck('name')->toArray();
                    $item['description'] = '擅长';
                    foreach ($skillTags as $skillTag) {
                        $item['description'] .= '"'.$skillTag.'"';
                    }
                } elseif ($tag->answers > 0){
                    $answerTag = Tag::find($tag->tag_id);
                    $item['description'] = '曾在"'.$answerTag->name.'"下有回答';
                } else {
                    $item['description'] = '向您推荐';
                }
            } else {
                $item['description'] = '向您推荐';
            }
            $item['id'] = $info->id;
            $item['uuid'] = $info->uuid;
            $item['name'] = $info->name;
            $item['avatar_url'] = $info->avatar;
            $item['is_expert'] = $info->is_expert;
            $item['is_invited'] = 0;
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
        $this->doing($answer->user,'question_answer_rejected',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id);
        /*修改问题邀请表的回答状态*/
        QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>QuestionInvitation::STATUS_REJECTED]);
        self::$needRefresh = true;
        return self::createJsonData(true,['question_id'=>$data['question_id'],'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
    }

    //我的提问列表
    public function myList(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $type = $request->input('type',0);
        $uuid = $request->input('uuid');
        $returnType = $request->input('return_type');

        $loginUser = $request->user();
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
            if ($loginUser->id != $user->id) {
                $query = $user->questions()->where('hide',0)->where(function($query) {$query->where('is_recommend',1)->where('question_type',1)->orWhere('question_type',2);});
            } else {
                $query = $user->questions();
            }
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
        $questions = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));

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
                'user_avatar_url' => $question->user->avatar,
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
        if ($returnType) {
            $return = $questions->toArray();
            $return['data'] = $list;
            return self::createJsonData(true,$return);
        }
        return self::createJsonData(true,$list);
    }


    //问答社区列表
    public function questionList(Request $request, JWTAuth $JWTAuth){
        $orderBy = $request->input('order_by',3);//1最新，2最热，3综合，
        $filter = $request->input('filter',1);//1悬赏大厅，2热门
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        if ($filter == 1) {
            $filterName = '悬赏大厅';
            $query = Question::where('question_type',2)->where('status','<=',6);
        } else {
            $filterName = '热门';
            $query = Question::where('is_recommend',1)->where('question_type',1)->orWhere('question_type',2);
        }
        $queryOrderBy = 'questions.rate';
        switch ($orderBy) {
            case 1:
                //最新
                $queryOrderBy = 'questions.created_at';
                break;
            case 2:
                //最热
                $queryOrderBy = 'questions.hot_rate';
                break;
            case 3:
                //综合
                $queryOrderBy = 'questions.rate';
                break;
        }
        $questions = $query->orderBy($queryOrderBy,'desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $this->doing($user,Doing::ACTION_VIEW_QUESTION_LIST,'',0,$filterName.'-核心页面');
        $return = $questions->toArray();
        $list = [];
        foreach($questions as $question){
            $list[] = $question->formatListItem();
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    //专业问答-推荐问答列表
    public function majorList(Request $request,JWTAuth $JWTAuth) {
        $tag_id = $request->input('tag_id',0);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }

        $query = Question::where('questions.is_recommend',1)->where('questions.question_type',1);

        if ($tag_id) {
            $query = $query->leftJoin('taggables','questions.id','=','taggables.taggable_id')->where('taggables.taggable_type','App\Models\Question')->where('taggables.taggable_id',$tag_id);
        }

        $questions = $query->orderBy('questions.rate','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $questions->toArray();
        $list = [];
        foreach($questions as $question){
            /*已解决问题*/
            $bestAnswer = [];
            if($question->status >= 6 ){
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
            }
            $supporters = [];
            $is_pay_for_view = false;
            $is_self = $user->id == $question->user_id;
            $is_answer_author = false;

            if ($bestAnswer) {
                //是否回答者
                if ($bestAnswer->user_id == $user->id) {
                    $is_answer_author = true;
                }
                $support_uids = Support::where('supportable_type','=',get_class($bestAnswer))->where('supportable_id','=',$bestAnswer->id)->take(20)->pluck('user_id');
                if ($support_uids) {
                    foreach ($support_uids as $support_uid) {
                        $supporter = User::find($support_uid);
                        $supporters[] = [
                            'name' => $supporter->name,
                            'uuid' => $supporter->uuid
                        ];
                    }
                }
                $payOrder = $bestAnswer->orders()->where('user_id',$user->id)->where('return_param','view_answer')->first();
                if ($payOrder) {
                    $is_pay_for_view = true;
                }
            }

            $list[] = [
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
                'answer_user_is_expert' => $bestAnswer && $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->avatar : '',
                'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : '',
                'comment_number' => $bestAnswer ? $bestAnswer->comments : 0,
                'average_rate'   => $bestAnswer ? $bestAnswer->getFeedbackRate() : 0,
                'support_number' => $bestAnswer ? $bestAnswer->supports : 0,
                'supporter_list' => $supporters,
                'is_pay_for_view' => ($is_self || $is_answer_author || $is_pay_for_view)
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    //互动问答-问答列表
    public function commonList(Request $request,JWTAuth $JWTAuth) {
        $tag_id = $request->input('tag_id',0);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $query = Question::where('questions.question_type',2);

        if ($tag_id) {
            $query = $query->leftJoin('taggables','questions.id','=','taggables.taggable_id')->where('taggables.taggable_type','App\Models\Question')->where('taggables.taggable_id',$tag_id);
        }

        $questions = $query->orderBy('questions.rate','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $questions->toArray();
        $list = [];
        foreach($questions as $question){
            $is_followed_question = 0;
            $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
            if ($attention_question) {
                $is_followed_question = 1;
            }
            $answer_uids = Answer::where('question_id',$question->id)->select('user_id')->distinct()->take(5)->pluck('user_id')->toArray();
            $answer_users = [];
            if ($answer_uids) {
                $answer_users = User::whereIn('id',$answer_uids)->select('uuid','name')->get()->toArray();
            }
            $list[] = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'user_id' => $question->user_id,
                'description'  => $question->title,
                'tags' => $question->tags()->select('tag_id','name')->get()->toArray(),
                'hide' => $question->hide,
                'price' => $question->price,
                'status' => $question->status,
                'created_at' => (string)$question->created_at,
                'question_username' => $question->hide ? '匿名' : $question->user->name,
                'question_user_is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
                'question_user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                'answer_num' => $question->answers,
                'answer_user_list' => $answer_users,
                'follow_num' => $question->followers,
                'is_followed_question' => $is_followed_question
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
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
    public function answerList(Request $request,JWTAuth $JWTAuth){
        $id = $request->input('question_id');
        $question = Question::find($id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $is_self = $user->id == $question->user_id;
        $answers = $question->answers()->orderBy('adopted_at','DESC')->orderBy('supports','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $answers->toArray();
        $return['data'] = [];
        foreach ($answers as $answer) {
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($answer->user))->where('source_id','=',$answer->user_id)->first();

            $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->first();
            $downvote = DownVote::where("user_id",'=',$user->id)->where('source_type','=',get_class($answer))->where('source_id','=',$answer->id)->first();

            $is_answer_author = false;
            $is_pay_for_view = false;
            if ($answer->adopted_at > 0) {
                //是否回答者
                if ($answer->user_id == $user->id) {
                    $is_answer_author = true;
                }
                //是否已经付过围观费
                $payOrder = $answer->orders()->where('user_id',$user->id)->where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->first();
                if ($payOrder) {
                    $is_pay_for_view = true;
                }
            } else {
                $is_pay_for_view = true;
            }
            $return['data'][] = [
                'id' => $answer->id,
                'user_id' => $answer->user_id,
                'uuid' => $answer->user->uuid,
                'user_name' => $answer->user->name,
                'user_avatar_url' => $answer->user->avatar,
                'title' => $answer->user->title,
                'company' => $answer->user->company,
                'is_expert' => $answer->user->userData->authentication_status == 1 ? 1 : 0,
                'is_best_answer' => $answer->adopted_at?true:false,
                'content' => ($is_self || $is_answer_author || $is_pay_for_view)?$answer->getContentText():'',
                'content_raw' => ($is_self || $is_answer_author || $is_pay_for_view)?$answer->content:'',
                'promise_time' => $answer->promise_time,
                'is_followed' => $attention?1:0,
                'is_supported' => $support?1:0,
                'is_downvoted' => $downvote?1:0,
                'support_number' => $answer->supports,
                'downvote_number' => $answer->downvotes,
                'view_number'    => $answer->views,
                'comment_number' => $answer->comments,
                'created_at' => $answer->created_at->diffForHumans()
            ];
        }
        return self::createJsonData(true,$return);
    }

    //推荐相关问题
    public function recommendUserQuestions(Request $request) {
        $user = $request->user();
        $perPage = $request->input('perPage',5);
        $skillTags = $user->userSkillTag()->pluck('tag_id')->toArray();
        $attentionTags = $user->attentions()->where('source_type','App\Models\Tag')->get()->pluck('source_id')->toArray();
        $attentionTags = array_unique(array_merge($attentionTags,$skillTags));
        $relatedQuestions = Question::correlationsPage($attentionTags,$perPage,2,[$user->id]);
        if ($relatedQuestions->count() <= 0) {
            $relatedQuestions = Question::recent($perPage,2,[$user->id]);
        }
        $return = $relatedQuestions->toArray();
        $list = [];
        foreach ($relatedQuestions as $relatedQuestion) {
            $is_answerd = Answer::where('user_id',$user->id)->where('question_id',$relatedQuestion->id)->first();
            if ($is_answerd) continue;
            $is_followed_question = 0;
            $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($relatedQuestion))->where('source_id','=',$relatedQuestion->id)->first();
            if ($attention_question) {
                $is_followed_question = 1;
            }
            $answer_uids = Answer::where('question_id',$relatedQuestion->id)->take(3)->pluck('user_id')->toArray();
            $answer_users = [];
            foreach ($answer_uids as $answer_uid) {
                $answer_user = User::find($answer_uid);
                $answer_users[] = [
                    'uuid' => $answer_user->uuid,
                    'avatar' => $answer_user->avatar
                ];
            }
            $list[$relatedQuestion->id] = [
                'id' => $relatedQuestion->id,
                'title' => $relatedQuestion->title,
                'question_type' => $relatedQuestion->question_type,
                'answer_number' => $relatedQuestion->answers,
                'follow_number' => $relatedQuestion->followers,
                'is_followed_question'   => $is_followed_question,
                'tags'  => $relatedQuestion->tags()->wherePivot('is_display',1)->select('tag_id','name')->get()->toArray(),
                'answer_users' => $answer_users
            ];
        }
        if (count($list) < $perPage) {
            $relatedQuestions = Question::recent(40,2,[$user->id]);
            foreach ($relatedQuestions as $relatedQuestion) {
                $is_answerd = Answer::where('user_id',$user->id)->where('question_id',$relatedQuestion->id)->first();
                if ($is_answerd) continue;
                $is_followed_question = 0;
                $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($relatedQuestion))->where('source_id','=',$relatedQuestion->id)->first();
                if ($attention_question) {
                    $is_followed_question = 1;
                }
                $answer_uids = Answer::where('question_id',$relatedQuestion->id)->take(3)->pluck('user_id')->toArray();
                $answer_users = [];
                foreach ($answer_uids as $answer_uid) {
                    $answer_user = User::find($answer_uid);
                    $answer_users[] = [
                        'uuid' => $answer_user->uuid,
                        'avatar' => $answer_user->avatar
                    ];
                }
                $list[$relatedQuestion->id] = [
                    'id' => $relatedQuestion->id,
                    'title' => $relatedQuestion->title,
                    'question_type' => $relatedQuestion->question_type,
                    'answer_number' => $relatedQuestion->answers,
                    'follow_number' => $relatedQuestion->followers,
                    'is_followed_question'   => $is_followed_question,
                    'tags'  => $relatedQuestion->tags()->wherePivot('is_display',1)->select('tag_id','name')->get()->toArray(),
                    'answer_users' => $answer_users
                ];
                if (count($list) >= $perPage) break;
            }
        }
        $return['data'] = array_values($list);
        $return['user_skill_tags'] = $skillTags;
        return self::createJsonData(true,$return);
    }

    //生成悬赏问答长图
    public function getShareImage(Request $request) {
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $question = Question::findOrFail($request->input('id'));
        $user = $request->user();
        $url = RateLimiter::instance()->hGet('question-shareImage',$question->id.'-'.$user->id);

        if(!$url){
            $snappy = App::make('snappy.image');
            $snappy->setOption('width',1125);
            $image = $snappy->getOutput(config('app.url').'/service/getQuestionShareImage/'.$question->id.'/'.$user->id);
            $file_name = 'question/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
            Storage::disk('oss')->put($file_name,$image);
            $url = Storage::disk('oss')->url($file_name);
            RateLimiter::instance()->hSet('question-shareImage',$question->id.'-'.$user->id, $url);
        }
        return self::createJsonData(true,['url'=>$url]);
    }

    protected function checkUserInfoPercent($user){
        return;
        //字段完成度为90%才能创建问题
        $percent = $user->getInfoCompletePercent();
        $valid_percent = config('inwehub.user_info_valid_percent',90);
        if($percent < $valid_percent){
            throw new ApiException(ApiException::ASK_NEED_USER_INFORMATION);
        }
    }

}
