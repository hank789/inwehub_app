<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Attention;
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
        'description' => 'required|min:15|max:65535',
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
        $answerContent = clean($request->input('description'));
        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $question_id,
            'content'  => $answerContent,
            'adopted_at' => date('Y-m-d H:i:s'),
            'status'   => 1,
        ];
        $answer = Answer::create($data);
        if($answer){

            /*用户回答数+1*/
            $loginUser->userData()->increment('answers');

            /*问题回答数+1*/
            $question->increment('answers');

            //问题变为已回答
            $question->answered();

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
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at],true,$message);
            }
        }

        throw new ApiException(ApiException::ERROR);
    }


    public function edit($id,Request $request)
    {
        $answer = Answer::findOrFail($id);

        if($answer->user_id !== $request->user()->id && !$request->user()->isRole('admin')){
            abort(403);
        }
        /*编辑回答时效控制*/
        if( !$request->user()->isRole('admin') && Setting()->get('edit_answer_timeout') ){
            if( $answer->created_at->diffInMinutes() > Setting()->get('edit_answer_timeout') ){
                return $this->showErrorMsg(route('ask.question.detail',['id'=>$answer->question_id]),'你已超过回答可编辑的最大时长，不能进行编辑了。如有疑问请联系管理员!');
            }

        }

        return view("theme::question.edit_answer")->with('answer',$answer);
    }


    /*修改问题内容*/
    public function update($id,Request $request)
    {
        $answer = Answer::findOrFail($id);

        if($answer->user_id !== $request->user()->id && !$request->user()->isRole('admin')){
            abort(403);
        }

        $request->flash();

        $this->validate($request,$this->validateRules);

        $answer->content = clean($request->input('content'));
        $answer->status = 1;

        $answer->save();

        return $this->success(route('ask.answer.detail',['question_id'=>$answer->question_id,'id'=>$answer->id]),"回答编辑成功");

    }


    public function adopt($id,Request $request)
    {
        $answer = Answer::findOrFail($id);

        if(($request->user()->id !== $answer->question->user_id) && !$request->user()->isRole('admin')  ){
            abort(403);
        }

        /*防止重复采纳*/
        if($answer->adopted_at>0){
            return $this->error(route('ask.question.detail',['question_id'=>$answer->question_id]),'该回答已被采纳，不能重复采纳');
        }


        DB::beginTransaction();
        try{

            $answer->adopted_at = Carbon::now();
            $answer->save();

            $answer->question->status = 2;
            $answer->question->save();

            $answer->user->userData->increment('adoptions');

            /*悬赏处理*/
            $this->credit($answer->user_id,'answer_adopted',($answer->question->price+Setting()->get('coins_adopted',0)),Setting()->get('credits_adopted'),$answer->question->id,$answer->question->title);

            UserTag::multiIncrement($request->user()->id,$answer->question->tags()->get(),'adoptions');
            $this->notify($request->user()->id,$answer->user_id,'adopt_answer',$answer->question_title,$answer->question_id);
            DB::commit();
            /*发送邮件通知*/
            if($answer->user->allowedEmailNotify('adopt_answer')){
                $emailSubject = '您对于问题「'.$answer->question_title.'」的回答被采纳了！';
                $emailContent = "您对于问题「".$answer->question_title."」的回答被采纳了！<br /> 点击此链接查看详情  →  ".route('ask.question.detail',['question_id'=>$answer->question_id]);
                $this->sendEmail($answer->user->email,$emailSubject,$emailContent);
            }

            return $this->success(route('ask.question.detail',['question_id'=>$answer->question_id]),"回答采纳成功!".get_credit_message(Setting()->get('credits_adopted'),Setting()->get('coins_adopted')));

        }catch (\Exception $e) {
            echo $e->getMessage();
            DB::rollBack();
        }
        return $this->error(route('ask.question.detail',['question_id'=>$answer->question_id]),"回答采纳失败，请稍后再试！");


    }


    /**
     * 回答详情查看
     */
    public function detail($question_id,$id,Request $request)
    {

        $question = Question::findOrFail($question_id);

        /*问题查看数+1*/
        $question->increment('views');

        $answer = $question->answers()->find($id);

        /*设置通知为已读*/
        if($request->user()){
            $this->readNotifications($answer->id,'answer');
        }

        /*相关问题*/
        $relatedQuestions = Question::correlations($question->tags()->pluck('tag_id'));
        return view("theme::answer.detail")->with('question',$question)
            ->with('answer',$answer)
            ->with('relatedQuestions',$relatedQuestions);
    }



}
