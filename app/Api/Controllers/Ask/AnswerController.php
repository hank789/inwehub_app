<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Setting;
use App\Models\UserTag;
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

        $question_id = $request->input('question_id');
        $question = Question::find($question_id);

        if(empty($question)){
            abort(404);
        }
        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->where('status',0)->first();
        if(empty($question_invitation)){
            abort(404);
        }

        $this->validate($request,$this->validateRules);
        $promise_time = $request->input('promise_time');

        $answerContent = clean($request->input('description'));
        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $question_id,
            'content'  => $answerContent,
        ];
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

        if($question->status == 4){
            //已确认待回答
            $answer = Answer::where('status',3)->first();
        }else{
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

                $answer->status = 1;
                $answer->save();

                UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'answers');

                /*记录动态*/
                $this->doing($answer->user_id,'answered',get_class($question),$question->id,$question->title,$answer->content);

                /*记录通知*/
                $this->notify($answer->user_id,$question->user_id,'answer',$question->title,$question->id,$answer->content);

                /*回答后通知关注问题*/
                if(intval($request->input('followed'))){
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

                /*记录积分*/
                if($this->credit($request->user()->id,'answer',$question->price ?? Setting()->get('coins_answer'),Setting()->get('credits_answer'),$question->id,$question->title)){
                    $message = '回答成功! '.get_credit_message(Setting()->get('credits_answer'),Setting()->get('coins_answer'));
                    return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at],ApiException::SUCCESS,$message);
                }
            }else{
                //问题变为待回答
                $question->confirmedAnswer();
                /*记录动态*/
                $this->doing($answer->user_id,'answer_confirmed',get_class($question),$question->id,$question->title,$answer->content);
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
            }
        }

        throw new ApiException(ApiException::ERROR);
    }

    //我的提问列表
    public function myList(Request $request)
    {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = QuestionInvitation::where('user_id','=',$request->user()->id)->whereIn('status',[0,1]);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }

        $question_invitations = $query->orderBy('id','DESC')->paginate(10);
        $list = [];
        foreach($question_invitations as $question_invitation){
            $question = Question::find($question_invitation->question_id);
            $status_description = '';
            $answer_promise_time = '';
            switch($question->status){
                case 2:
                    //已分配待确认
                    $status_description = '您的问题来啦,请速速点击前往应答';
                    break;
                case 4:
                    //已确认待回答
                    $answer = Answer::where('status',3)->first();
                    $answer_promise_time = $answer->promise_time;
                    $status_description = promise_time_format($answer_promise_time).',点击前往回答';
                    break;
                case 6:
                    //已回答待点评
                    $status_description = '您已提交回答,等待对方评价';
                    break;
                case 7:
                    //已点评
                    $status_description = '对方已点评,点击前往查看评价';
                    break;
            }
            $list[] = [
                'id' => $question->id,
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

        Feedback::create([
            'user_id' => $request->user()->id,
            'source_id' => $request->input('answer_id'),
            'source_type' => get_class($answer),
            'star' => $request->input('rate_star'),
            'content' => $request->input('description'),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $answer->question()->update(['status'=>7]);

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
