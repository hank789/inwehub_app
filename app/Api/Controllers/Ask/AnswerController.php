<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Events\Frontend\System\Push;
use App\Exceptions\ApiException;
use App\Logic\MoneyLogLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Feedback;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\Settlement;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Setting;
use App\Models\Task;
use App\Models\UserTag;
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

        /*防灌水检查*/
        if( Setting()->get('answer_limit_num') > 0 ){
            $questionCount = $this->counter('answer_num_'. $loginUser->id);
            if( $questionCount > Setting()->get('answer_limit_num')){
                return self::createJsonData(false,[],ApiException::VISIT_LIMIT,'你已超过每小时回答限制数'.Setting()->get('answer_limit_num').'，请稍后再进行该操作，如有疑问请联系管理员!');
            }
        }

        if(RateLimiter::instance()->increase('question:answer:create',$loginUser->id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $question_id = $request->input('question_id');
        $question = Question::find($question_id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }
        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->where('status',0)->first();
        if(empty($question_invitation)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $this->validate($request,$this->validateRules);
        $lock_key = 'question_answer_action';
        RateLimiter::instance()->lock_acquire($lock_key,1,20);
        //检查问题是否已经被其它人回答
        $exit_answers = Answer::where('question_id',$question_id)->whereIn('status',[1,3])->where('user_id','!=',$loginUser->id)->get()->last();
        if($exit_answers){
            RateLimiter::instance()->lock_release($lock_key);
            throw new ApiException(ApiException::ASK_QUESTION_ALREADY_CONFIRMED);
        }

        $promise_time = $request->input('promise_time');

        $answerContent = trim($request->input('description'));
        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $question_id,
            'content'  => $answerContent,
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
                $data['status'] = 3;
                $data['content'] = '承诺在:'.$data['promise_time'].'前回答该问题';
            }else{
                $data['adopted_at'] = date('Y-m-d H:i:s');
                $data['status'] = 1;
            }
            $answer = Answer::create($data);
        }
        RateLimiter::instance()->lock_release($lock_key);

        if($answer){
            if(empty($promise_time)){
                /*用户回答数+1*/
                $loginUser->userData()->increment('answers');

                /*问题回答数+1*/
                $question->increment('answers');

                //问题变为已回答
                $question->answered();

                $answer->status = 1;
                $answer->content = $answerContent;
                $answer->adopted_at = date('Y-m-d H:i:s');
                $answer->save();
                //任务变为已完成
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[]);

                $this->task($question->user_id,get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK);

                UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'answers');

                /*记录动态*/
                $this->doing($answer->user_id,'question_answered',get_class($question),$question->id,$question->title,$answer->content);

                /*记录通知*/
                $this->notify($answer->user_id,$question->user_id,'question_answered',$question->title,$question->id,$answer->content);

                //推送通知
                event(new Push($question->user,'您的提问专家已回答,请前往点评',$question->title,['object_type'=>'question','object_id'=>$question->id]));

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
                QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>1]);

                $this->counter( 'answer_num_'. $answer->user_id , 1 , 3600 );
                $message = '回答成功!';
                /*记录积分*/
                $this->credit($request->user()->id,'answer',$answer->id,$answer->content);
                //首次回答额外积分
                if($loginUser->userData->answers == 1)
                {
                    $this->credit($request->user()->id,'first_answer',$answer->id,$answer->content);
                }

                //进入结算中心
                Settlement::answerSettlement($answer);
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at],ApiException::SUCCESS,$message);

            }else{
                //问题变为待回答
                $question->confirmedAnswer();
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[],[$request->user()->id]);
                /*记录动态*/
                $this->doing($answer->user_id,'question_answer_confirmed',get_class($question),$question->id,$question->title,$answer->content);
                //推送通知
                event(new Push($question->user,'您的提问专家已响应,点击查看',$question->title,['object_type'=>'question','object_id'=>$question->id]));

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
        }else{
            $query = $query->where('id','>',0);
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
                'question_id' => $question->id,
                'user_id' => $question->user_id,
                'user_name' => $question->hide ? '匿名' : $question->user->name,
                'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl(),
                'description'  => $question->title,
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
        $answer = Answer::find($request->input('answer_id'));
        if(empty($answer)){
            abort(404);
        }
        $loginUser = $request->user();
        if($answer->question->user->id != $loginUser->id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        if(RateLimiter::instance()->increase('question:answer:feedback',$loginUser->id,10,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        Feedback::create([
            'user_id' => $loginUser->id,
            'source_id' => $request->input('answer_id'),
            'source_type' => get_class($answer),
            'star' => $request->input('rate_star'),
            'content' => $request->input('description'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $answer->question()->update(['status'=>7]);

        $this->finishTask(get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK,[$request->user()->id]);
        //推送通知
        event(new Push($answer->user,'您的回答已点评,点击查看',$answer->content,['object_type'=>'answer','object_id'=>$answer->question_id]));


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
}
