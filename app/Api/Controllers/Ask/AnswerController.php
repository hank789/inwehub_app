<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Answer\PayForView;
use App\Exceptions\ApiException;
use App\Logic\PayQueryLogic;
use App\Logic\QuillLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Feedback;
use App\Models\Pay\Order;
use App\Models\Pay\Ordergable;
use App\Models\Pay\Settlement;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Support;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionAnswered;
use App\Notifications\NewQuestionConfirm;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AnswerController extends Controller
{

    /*问题创建校验*/
    protected $validateRules = [
        'question_id' => 'required'
    ];


    public function saveDraft(Request $request) {
        $loginUser = $request->user();
        $key = 'draft:answer:question:'.$request->get('question_id').':user:'.$loginUser->id;
        Cache::put($key,$request->get('description'));
        return self::createJsonData(true);
    }

    public function getDraft(Request $request) {
        $loginUser = $request->user();
        $key = 'draft:answer:question:'.$request->get('question_id').':user:'.$loginUser->id;
        $draftContent = Cache::get($key);
        return self::createJsonData(true,['draftContent'=>$draftContent]);
    }

    //回答详情
    public function info(Request $request){
        $id = $request->input('id');
        $answer = Answer::find($id);

        if(empty($answer)){
            throw new ApiException(ApiException::ASK_ANSWER_NOT_EXIST);
        }
        $user = $request->user();
        $question = $answer->question;

        $is_self = $user->id == $question->user_id;
        $is_answer_author = false;
        $is_pay_for_view = false;


        //已经回答的问题其他人都能看,没回答的问题只有邀请者才能看(付费专业问答)
        if ($question->question_type == 1 && $question->status < 6) {
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

        //是否回答者
        if ($answer->user_id == $user->id) {
            $is_answer_author = true;
        }
        //是否已经付过围观费
        $payOrder = $answer->orders()->where('user_id',$user->id)->where('return_param','view_answer')->first();
        if ($payOrder) {
            $is_pay_for_view = true;
        }
        $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($answer->user))->where('source_id','=',$answer->user_id)->first();

        $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->first();

        $collect = Collection::where('user_id',$user->id)->where('source_type','=',get_class($answer))->where('source_id','=',$answer->id)->first();

        $support_uids = Support::where('supportable_type','=',get_class($answer))->where('supportable_id','=',$answer->id)->take(20)->pluck('user_id');
        $supporters = [];
        if ($support_uids) {
            $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
        }

        $answers_data = [
            'id' => $answer->id,
            'user_id' => $answer->user_id,
            'uuid' => $answer->user->uuid,
            'user_name' => $answer->user->name,
            'user_avatar_url' => $answer->user->avatar,
            'title' => $answer->user->title,
            'company' => $answer->user->company,
            'is_expert' => $answer->user->userData->authentication_status == 1 ? 1 : 0,
            'content' => ($is_self || $is_answer_author || $is_pay_for_view || $question->question_type == 2) ? $answer->content : '',
            'promise_time' => $answer->promise_time,
            'is_followed' => $attention?1:0,
            'is_supported' => $support?1:0,
            'is_collected' => $collect?1:0,
            'support_number' => $answer->supports,
            'view_number'    => $answer->views,
            'comment_number' => $answer->comments,
            'collect_num' => $answer->collections,
            'average_rate'   => $answer->getFeedbackRate(),
            'created_at' => (string)$answer->created_at,
            'supporter_list' => $supporters
        ];


        $attention_question_user = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question->user))->where('source_id','=',$question->user_id)->first();

        $question_data = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'uuid' => $question->hide ? '' : $question->user->uuid,
            'question_type' => $question->question_type,
            'user_name' => $question->hide ? '匿名' : $question->user->name,
            'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
            'title' => $question->hide ? '保密' : $question->user->title,
            'company' => $question->hide ? '保密' : $question->user->company,
            'is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
            'is_followed' => $question->hide ? 0 : ($attention_question_user?1:0),
            'user_description' => $question->hide ? '':$question->user->description,
            'description'  => $question->title,
            'tags' => $question->tags()->get()->toArray(),
            'hide' => $question->hide,
            'price' => $question->price,
            'data'  => $question->data,
            'status' => $question->status,
            'status_description' => $question->statusHumanDescription($user->id),
            'promise_answer_time' => $answer->promise_time,
            'answer_num' => $question->answers,
            'follow_num' => $question->followers,
            'created_at' => (string)$question->created_at
        ];
        $answer->increment('views');


        return self::createJsonData(true,[
            'question'=>$question_data,
            'answer'=>$answers_data]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $loginUser = $request->user();

        if(RateLimiter::instance()->increase('question:answer:create',$loginUser->id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $this->validate($request,$this->validateRules);

        $question_id = $request->input('question_id');
        $question = Question::find($question_id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $lock_key = 'question_answer_action';
        $doing_prefix = '';

        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->first();
        if ($question->question_type == 1) {
            if(empty($question_invitation)){
                throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
            }

            RateLimiter::instance()->lock_acquire($lock_key,1,20);
            if($question_invitation->status == QuestionInvitation::STATUS_ANSWERED){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_ANSWERED);
            }
            //检查问题是否已经被其它人回答
            $exit_answers = Answer::where('question_id',$question_id)->whereIn('status',[1,3])->where('user_id','!=',$loginUser->id)->get()->last();
            if($exit_answers){
                RateLimiter::instance()->lock_release($lock_key);
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_CONFIRMED);
            }
        }

        if ($question->question_type == 2) {
            //互动问答只能回答一次
            $exit_answers = Answer::where('question_id',$question_id)->where('user_id',$loginUser->id)->get()->last();
            if ($exit_answers){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_ANSWERED);
            }
        }

        $promise_time = $request->input('promise_time');

        $answerContent = $request->input('description');

        if(empty($promise_time) && strlen(trim($answerContent)) <= 4){
            throw new ApiException(ApiException::ASK_ANSWER_CONTENT_TOO_SHORT);
        }

        $answerContent = QuillLogic::parseImages($answerContent);
        if ($answerContent === false){
            $answerContent = $request->input('description');
        }

        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $question_id,
            'content'  => $answerContent,
            'status'   => Answer::ANSWER_STATUS_FINISH,
            'device'       => intval($request->input('device'))
        ];

        if ($question->question_type == 1) {
            //付费专业问答
            //先检查是否已有回答
            $answer = Answer::where('question_id',$question_id)->where('user_id',$loginUser->id)->get()->last();

            if(!$answer){
                if($promise_time){
                    if(strlen($promise_time) != 4) {
                        throw new ApiException(ApiException::ASK_ANSWER_PROMISE_TIME_INVALID);
                    }
                    $hours = substr($promise_time,0,2);
                    $minutes = substr($promise_time,2,2);
                    $data['promise_time'] = date('Y-m-d H:i:00',strtotime('+ '.$hours.' hours + '.$minutes.' minutes'));
                    $data['status'] = Answer::ANSWER_STATUS_PROMISE;
                    $data['content'] = '承诺在:'.$data['promise_time'].'前回答该问题';
                }else{
                    $data['adopted_at'] = date('Y-m-d H:i:s');
                    $data['status'] = Answer::ANSWER_STATUS_FINISH;
                }
                $answer = Answer::create($data);
            }elseif($promise_time){
                //重复响应
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_SELF_CONFIRMED);
            }
        } else {
            $doing_prefix = 'free_';
            //互助问答
            $answer = Answer::create($data);
        }


        if($answer){
            if(empty($promise_time)){
                /*用户回答数+1*/
                $loginUser->userData()->increment('answers');

                /*问题回答数+1*/
                $question->increment('answers');

                //问题变为已回答
                $question->answered();

                if ($question->question_type == 1) {
                    $answer->status = Answer::ANSWER_STATUS_FINISH;
                    $answer->content = $answerContent;
                    $answer->adopted_at = date('Y-m-d H:i:s');
                    $answer->save();

                    $this->task($question->user_id,get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK);
                }

                //任务变为已完成
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[]);
                if ($question_invitation) $this->finishTask(get_class($question_invitation),$question_invitation->id,Task::ACTION_TYPE_INVITE_ANSWER,[]);


                UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'answers');

                /*记录动态*/
                $this->doing($answer->user_id,$doing_prefix.'question_answered',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);

                /*记录通知*/
                if ($question->user_id != $answer->user_id)
                    $question->user->notify(new NewQuestionAnswered($question->user_id,$question,$answer));

                /*回答后通知关注问题*/
                if(true){
                    $attention = Attention::where("user_id",'=',$request->user()->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->count();
                    if($attention===0){
                        $data = [
                            'user_id'     => $request->user()->id,
                            'source_id'   => $question->id,
                            'source_type' => get_class($question),
                            'subject'  => $question->title,
                        ];
                        Attention::create($data);

                        $question->increment('followers');
                    }
                }
                /*修改问题邀请表的回答状态*/
                QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>QuestionInvitation::STATUS_ANSWERED]);
                RateLimiter::instance()->lock_release($lock_key);

                $this->counter( 'answer_num_'. $answer->user_id , 1 , 3600 );
                $message = '回答成功!';

                //首次回答额外积分
                if($loginUser->userData->answers == 1)
                {
                    if ($question->question_type == 1) {
                        $credit_key = Credit::KEY_FIRST_ANSWER;
                    } else {
                        $credit_key = Credit::KEY_FIRST_COMMUNITY_ANSWER;
                    }
                } else {
                    if ($question->question_type == 1) {
                        $credit_key = Credit::KEY_ANSWER;
                    } else {
                        $credit_key = Credit::KEY_COMMUNITY_ANSWER;
                    }
                }

                $this->credit($request->user()->id,$credit_key,$answer->id,$answer->getContentText());


                //进入结算中心
                Settlement::answerSettlement($answer);
                Settlement::questionSettlement($question);
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at],ApiException::SUCCESS,$message);

            }else{
                //问题变为待回答
                $question->confirmedAnswer();
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[],[$request->user()->id]);
                /*修改问题邀请表的回答状态*/
                QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>QuestionInvitation::STATUS_CONFIRMED]);
                /*记录动态*/
                $this->doing($answer->user_id,'question_answer_confirmed',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);
                RateLimiter::instance()->lock_release($lock_key);
                $question->user->notify(new NewQuestionConfirm($question->user_id,$question,$answer));
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
            }
        }

        throw new ApiException(ApiException::ERROR);
    }


    public function update(Request $request){
        $loginUser = $request->user();

        if(RateLimiter::instance()->increase('question:answer:update',$loginUser->id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $validateRules = [
            'answer_id'   => 'required|integer',
            'description' => 'required|min:10',
        ];
        $this->validate($request,$validateRules);
        $answer = Answer::where('id',$request->input('answer_id'))->where('user_id',$loginUser->id)->first();
        if (!$answer) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $answerContent = $request->input('description');

        if(strlen(trim($answerContent)) <= 4){
            throw new ApiException(ApiException::ASK_ANSWER_CONTENT_TOO_SHORT);
        }

        $answerContent = QuillLogic::parseImages($answerContent);
        if ($answerContent === false){
            $answerContent = $request->input('description');
        }
        $answer->content = $answerContent;
        $answer->save();
        return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id], ApiException::SUCCESS,'修改成功');
    }

    //我的回答列表
    public function myList(Request $request)
    {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $type = $request->input('type',0);
        $uuid = $request->input('uuid');
        $user = $request->user();
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        }
        $query = Answer::where('user_id','=',$user->id);

        switch($type){
            case 1:
                //未完成
                $query = $query->where('status',3);
                break;
            case 2:
                //已完成
                $query = $query->where('status',1);
                break;
            default:
                $query = $query->whereIn('status',[1,3]);
                break;
        }

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }

        $answers = $query->orderBy('id','DESC')->paginate(Config::get('api_data_page_size'));
        $list = [];
        foreach($answers as $answer){
            $question = Question::find($answer->question_id);
            $answer_promise_time = '';

            $list[] = [
                'id' => $answer->id,
                'question_type' => $question->question_type,
                'question_id' => $question->id,
                'user_id' => $question->user_id,
                'user_name' => $question->hide ? '匿名' : $question->user->name,
                'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                'description'  => $question->getFormatTitle(),
                'hide' => $question->hide,
                'price' => $question->price,
                'status' => $question->status,
                'status_description' => $question->statusHumanDescription($question->user_id),
                'created_at' => (string)$question->created_at,
                'answer_promise_time' =>  $answer_promise_time
            ];
        }
        return self::createJsonData(true,$list);
    }


    public function feedback(Request $request)
    {
        $validateRules = [
            'answer_id' => 'required',
            'description' => 'required|max:500',
            'rate_star' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $answer = Answer::findOrFail($request->input('answer_id'));
        $question = $answer->question;

        $loginUser = $request->user();

        if(RateLimiter::instance()->increase('question:answer:feedback',$loginUser->id,10,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        //自己不能评价自己的回答
        if ($loginUser->id == $answer->user_id) {
            throw new ApiException(ApiException::ASK_FEEDBACK_SELF_ANSWER);
        }

        if ($loginUser->id == $question->user_id) {
            $feedback_type = 1;//提问者点评
            $action = Credit::KEY_RATE_ANSWER;
        } else {
            $feedback_type = 2;//围观者点评
            $action = Credit::KEY_FEEDBACK_RATE_ANSWER;
        }

        //防止重复评价
        $exist = Feedback::where('user_id',$loginUser->id)
            ->where('source_id',$request->input('answer_id'))
            ->where('source_type',get_class($answer))
            ->first();
        if ($exist) {
            throw new ApiException(ApiException::ASK_ANSWER_FEEDBACK_EXIST);
        }

        $feedback = Feedback::create([
            'user_id' => $loginUser->id,
            'source_id' => $request->input('answer_id'),
            'source_type' => get_class($answer),
            'star' => $request->input('rate_star'),
            'to_user_id' => $answer->user_id,
            'content' => $request->input('description'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($question->question_type == 1 && $feedback_type == 1) $answer->question()->update(['status'=>7]);

        $this->finishTask(get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK,[$request->user()->id]);

        $this->doing($loginUser->id,'question_answer_feedback',get_class($answer),$answer->id,'回答评价',$feedback->content,$feedback->id,$answer->user_id,$answer->getContentText());

        $this->credit($loginUser->id,$action,$answer->id,'回答评价');

        event(new \App\Events\Frontend\Answer\Feedback($feedback->id));
        return self::createJsonData(true,array_merge($request->all(),['feedback_type'=>$feedback_type]));
    }

    public function feedbackInfo(Request $request){
        $validateRules = [
            'answer_id' => 'required',
        ];
        $this->validate($request,$validateRules);
        $answer = Answer::find($request->input('answer_id'));
        if(empty($answer)){
            abort(404);
        }
        $feedback = $answer->feedbacks()->orderBy('id','desc')->first();
        return self::createJsonData(true,[
            'answer_id' => $answer->id,
            'rate_star' => $feedback->star,
            'description' => $feedback->content,
            'create_time' => (string)$feedback->created_at
        ]);

    }



    //付费围观
    public function payForView(Request $request) {
        $validateRules = [
            'order_id'    => 'required|integer',
            'answer_id' => 'required|integer',
            'device' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        //查看支付订单是否成功
        $order = Order::find($request->input('order_id'));
        if(empty($order) && Setting()->get('need_pay_actual',1)){
            throw new ApiException(ApiException::ASK_PAYMENT_EXCEPTION);
        }

        //如果订单存在且状态为处理中,有可能还未回调
        if($order && $order->status == Order::PAY_STATUS_PROCESS && Setting()->get('need_pay_actual',1)){
            if (PayQueryLogic::queryWechatPayOrder($order->id)){

            } else {
                $data['status'] = 0;
                \Log::error('付费围观支付订单还在处理中',[$request->all()]);
                throw new ApiException(ApiException::ASK_PAYMENT_EXCEPTION);
            }
        }
        if ($order->return_param != 'view_answer') {
            throw new ApiException(ApiException::ASK_PAYMENT_EXCEPTION);
        }

        $answer = Answer::findOrFail($request->input('answer_id'));
        $loginUser = $request->user();
        if ($order->user_id != $loginUser->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        $payOrderGable = $answer->orders()->where('pay_order.id',$order->id)->first();
        //如果已经围观过了
        if ($payOrderGable){
            return self::createJsonData(true,[
                'question_id' => $answer->question_id,
                'answer_id'   => $answer->id,
                'content'     => $answer->content
            ]);
        }


        $answer->orders()->attach($order->id);
        $answer->increment('pay_for_views');

        //进入结算中心
        Settlement::payForViewSettlement($order);
        //记录动态
        //自动收藏
        Collection::create([
            'user_id'     => $loginUser->id,
            'source_id'   => $answer->id,
            'source_type' => get_class($answer),
            'subject'  => '付费围观',
        ]);
        $answer->increment('collections');
        //生成一条点评任务
        $this->task($loginUser->id,get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK);

        event(new PayForView($order));
        return self::createJsonData(true,[
            'question_id' => $answer->question_id,
            'answer_id'   => $answer->id,
            'content'     => $answer->content
        ]);
    }

    //我的围观列表
    public function myOnlookList(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = Doing::where('user_id','=',$request->user()->id)->where('action','pay_for_view_question_answer');

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }

        $doings = $query->orderBy('id','DESC')->paginate(Config::get('api_data_page_size'));

        $list = [];
        foreach ($doings as $doing) {
            $answer = Answer::find($doing->source_id);
            $question = $answer->question;
            $list[] = [
                'id' => $doing->id,
                'question_id'   => $question->id,
                'user_id' => $question->user_id,
                'description'  => $question->title,
                'answer_user_id' => $answer->user->id,
                'answer_username' => $answer->user->name,
                'answer_user_title' => $answer->user->title,
                'answer_user_company' => $answer->user->company,
                'answer_user_is_expert' => $answer->user->userData->authentication_status == 1 ? 1 : 0,
                'answer_user_avatar_url' => $answer->user->avatar
            ];
        }
        return self::createJsonData(true,$list);
    }

    //问答留言列表
    public function commentList(Request $request) {
        $validateRules = [
            'answer_id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();

        $source  = Answer::find($data['answer_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $user_id = $request->user()->id;
        //专业只有问题作者，回答者，付费围观的人才能看到回复
        $is_question_author = $user_id == $source->question->user_id;
        $is_answer_author = $user_id == $source->user_id;
        if ((($is_question_author || $is_answer_author) && $source->question->question_type == 1) || $source->question->question_type == 2) {

        } else {
            $payOrder = $source->orders()->where('return_param','view_answer')->first();
            if (!$payOrder) {
                return self::createJsonData(true, Comment::where('id',0)->simplePaginate(Config::get('api_data_page_size'))->toArray());
            }
        }

        $comments = $source->comments()
            ->where('parent_id', 0)
            ->orderBy('created_at','desc')
            ->simplePaginate(Config::get('api_data_page_size'));

        return self::createJsonData(true,  $comments->toArray());
    }

    //问答留言
    public function comment(Request $request) {
        /*问题创建校验*/
        $validateRules = [
            'answer_id'    => 'required|integer',
            'content' => 'required|max:10000',
        ];

        $this->validate($request,$validateRules);
        $data = $request->all();

        $source  = Answer::find($data['answer_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $data = [
            'user_id'     => $request->user()->id,
            'content'     => $data['content'],
            'parent_id'   => $request->input('parent_id',0),
            'source_id'   => $data['answer_id'],
            'source_type' => get_class($source),
            'to_user_id'  => 0,
            'status'      => 1,
            'supports'    => 0
        ];


        $comment = Comment::create($data);
        /*问题、回答、文章评论数+1*/
        $source->increment('comments');

        return self::createJsonData(true,$comment->toArray(),ApiException::SUCCESS,'评论成功');
    }

}
