<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Events\Frontend\System\Push;
use App\Exceptions\ApiException;
use App\Logic\MoneyLogLogic;
use App\Logic\PayQueryLogic;
use App\Logic\QuillLogic;
use App\Logic\WechatNotice;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Feedback;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Order;
use App\Models\Pay\Settlement;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Setting;
use App\Models\Task;
use App\Models\UserTag;
use App\Notifications\NewQuestionAnswered;
use App\Notifications\NewQuestionConfirm;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{

    /*问题创建校验*/
    protected $validateRules = [
        'question_id' => 'required'
    ];


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

        $question_id = $request->input('question_id');
        $question = Question::find($question_id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->first();
        if(empty($question_invitation)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $this->validate($request,$this->validateRules);
        $lock_key = 'question_answer_action';
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
            'device'       => intval($request->input('device'))
        ];

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

        if($answer){
            if(empty($promise_time)){
                /*用户回答数+1*/
                $loginUser->userData()->increment('answers');

                /*问题回答数+1*/
                $question->increment('answers');

                //问题变为已回答
                $question->answered();

                $answer->status = Answer::ANSWER_STATUS_FINISH;
                $answer->content = $answerContent;
                $answer->adopted_at = date('Y-m-d H:i:s');
                $answer->save();
                //任务变为已完成
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[]);

                $this->task($question->user_id,get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK);

                UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'answers');

                /*记录动态*/
                $this->doing($answer->user_id,'question_answered',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);

                /*记录通知*/
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
                    $this->credit($request->user()->id,Credit::KEY_FIRST_ANSWER,$answer->id,$answer->getContentText());
                } else {
                    /*记录积分*/
                    $this->credit($request->user()->id,Credit::KEY_ANSWER,$answer->id,$answer->getContentText());
                }

                //进入结算中心
                Settlement::answerSettlement($answer);
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

    //我的回答列表
    public function myList(Request $request)
    {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = Answer::where('user_id','=',$request->user()->id)->whereIn('status',[0,1,3]);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }

        $answers = $query->orderBy('id','DESC')->paginate(10);
        $list = [];
        foreach($answers as $answer){
            $question = Question::find($answer->question_id);
            $status_description = '';
            $answer_promise_time = '';
            switch($question->status){
                case 2:
                    //已分配待确认
                    $status_description = '待回答';
                    break;
                case 4:
                    //已确认待回答
                    $status_description = '待回答';
                    break;
                case 6:
                    //已回答待点评
                    $status_description = '已回答';
                    break;
                case 7:
                    //已点评
                    $status_description = '已回答';
                    break;
            }
            $list[] = [
                'id' => $answer->id,
                'question_type' => $question->question_type,
                'question_id' => $question->id,
                'user_id' => $question->user_id,
                'user_name' => $question->hide ? '匿名' : $question->user->name,
                'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                'description'  => $question->title,
                'answer_content' => $answer->status ==1 ? $answer->getContentText():'',
                'tags' => $question->tags()->pluck('name'),
                'hide' => $question->hide,
                'price' => $question->price,
                'status' => $question->status,
                'status_description' => $status_description,
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

        $loginUser = $request->user();
        if($answer->question->user->id != $loginUser->id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        if(RateLimiter::instance()->increase('question:answer:feedback',$loginUser->id,10,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
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

        $answer->question()->update(['status'=>7]);

        $this->finishTask(get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK,[$request->user()->id]);

        $this->doing($loginUser->id,'question_answer_feedback',get_class($answer),$answer->id,'回答评价',$feedback->content,$feedback->id,$answer->user_id,$answer->getContentText());

        event(new \App\Events\Frontend\Answer\Feedback($feedback->id));
        return self::createJsonData(true,$request->all());
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

        $answer = Answer::findOrFail($request->input('answer_id'));
        $loginUser = $request->user();
        if ($order->user_id != $loginUser->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $answer->orders()->attach($order->id);
        //进入结算中心
        Settlement::payForViewSettlement($order);
        //记录动态
        $this->doing($loginUser->user_id,'pay_for_view_question_answer',get_class($answer),$answer->id,$answer->question->title,'');

        return self::createJsonData(true,[
            'question_id' => $answer->question_id,
            'answer_id'   => $answer->id
        ]);
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
        //只有问题作者，回答者，付费围观的人才能看到回复
        $is_question_author = $user_id == $source->question->user_id;
        $is_answer_author = $user_id == $source->user_id;
        if (!($is_question_author || $is_answer_author)) {
            $payOrder = $source->orders()->where('return_param','view_answer')->first();
            if (!$payOrder) {
                return [];
            }
        }

        $comments = $source->comments()->orderBy('created_at','desc')->simplePaginate(10);
        $return = $comments->toArray();
        $return['data'] = [];

        foreach ($comments as $comment) {
            $return['data'][] = [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'user_name' => $comment->user->name,
                'user_avatar_url' => $comment->user->avatar,
                'content'   => $comment->content,
                'created_at' => date('Y/m/d H:i',strtotime($comment->created_at))
            ];
        }

        return self::createJsonData(true,  $return);
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
            'source_id'   => $data['answer_id'],
            'source_type' => get_class($source),
            'to_user_id'  => 0,
            'status'      => 1,
            'supports'    => 0
        ];


        $comment = Comment::create($data);
        /*问题、回答、文章评论数+1*/
        $comment->source()->increment('comments');

        return self::createJsonData(true,[
            'tips'=>'评论成功',
            'comment_id' => $comment->id,
            'created_at' => date('Y/m/d H:i',strtotime($comment->created_at)),
            'user_name'  => $request->user()->name
        ]);
    }

}
