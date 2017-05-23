<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Pay\Order;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        /*已解决问题*/
        $bestAnswers = [];
        if($question->status >= 6 ){
            $bestAnswers = $question->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get();
        }

        /*设置通知为已读*/
        if($request->user()){
            $this->readNotifications($question->id,'question');
        }
        //问题作者或邀请者才能看
        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->first();
        if(empty($question_invitation) && $request->user()->id != $question->user->id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        $question_data = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'user_name' => $question->hide ? '匿名' : $question->user->name,
            'user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl(),
            'description'  => $question->title,
            'tags' => $question->tags()->pluck('name'),
            'hide' => $question->hide,
            'price' => $question->price,
            'status' => $question->status,
            'status_description' => $question->statusHumanDescription($user->id),
            'created_at' => (string)$question->created_at
        ];

        $answers_data = [];

        foreach($bestAnswers as $bestAnswer){
            $answers_data[] = [
                'id' => $bestAnswer->id,
                'user_id' => $bestAnswer->user_id,
                'user_name' => $bestAnswer->user->name,
                'user_avatar_url' => $bestAnswer->user->getAvatarUrl(),
                'content' => $bestAnswer->content,
                'promise_time' => $bestAnswer->promise_time,
                'created_at' => (string)$bestAnswer->created_at
            ];
        }

        $timeline = $question->formatTimeline();

        //feedback
        $feedback_data = [];
        if($answers_data){
            $feedback = $bestAnswers->last()->feedbacks()->orderBy('id','desc')->first();
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
        /*防灌水检查*/
        if( Setting()->get('question_limit_num') > 0 ){
            $questionCount = $this->counter('question_num_'. $user->id);
            if( $questionCount > Setting()->get('question_limit_num')){
                return self::createJsonData(false,[],ApiException::VISIT_LIMIT,'你已超过每小时最大提问数'.Setting()->get('question_limit_num').'，如有疑问请联系管理员!');
            }
        }
        $this->checkUserInfoPercent($user);
        $tags = TagsLogic::loadTags(1,'');

        return self::createJsonData(true,$tags);
    }


    /*创建提问*/
    public function store(Request $request)
    {
        $loginUser = $request->user();

        /*防灌水检查*/
        if( Setting()->get('question_limit_num') > 0 ){
            $questionCount = $this->counter('question_num_'. $loginUser->id);
            if( $questionCount > Setting()->get('question_limit_num')){
                return self::createJsonData(false,[],ApiException::VISIT_LIMIT,'你已超过每小时最大提问数'.Setting()->get('question_limit_num').'，如有疑问请联系管理员!');
            }
        }

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
        ];

        //查看支付订单是否成功
        $order = Order::find($request->input('order_id'));
        if((empty($order) || $order->status != Order::PAY_STATUS_SUCCESS) && Setting()->get('need_pay_actual',1)){
            throw new ApiException(ApiException::ASK_PAYMENT_EXCEPTION);
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

            $doing_obj = $this->doing(0,'question_process',get_class($question),$question->id,$question->title,'');
            $waiting_second = rand(1,15);
            $doing_obj->created_at = date('Y-m-d H:i:s',strtotime('+ '.$waiting_second.' seconds'));
            $doing_obj->save();

            /*用户提问数+1*/
            $loginUser->userData()->increment('questions');
            UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'questions');
            $this->credit($request->user()->id,'ask',Setting()->get('coins_ask'),Setting()->get('credits_ask'),$question->id,$question->title);
            if($question->status == 1 ){
                $message = '发起提问成功! '.get_credit_message(Setting()->get('credits_ask'),Setting()->get('coins_ask'));
            }else{
                $message = 'ok';
            }

            $this->counter( 'question_num_'. $question->user_id , 1 , 3600 );

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

        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->where('status',0)->first();
        if(empty($question_invitation)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $request->input('question_id'),
            'content'  => $request->input('description',''),
            'status'   => 2,
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
        $this->doing($answer->user_id,'question_answer_rejected',get_class($question),$question->id,$question->title,$answer->content);
        /*修改问题邀请表的回答状态*/
        QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>2]);

        return self::createJsonData(true,['question_id'=>$data['question_id'],'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
    }

    //我的提问列表
    public function myList(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = $request->user()->questions();
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
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
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->getAvatarUrl() : '',
                'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : ''
            ];
        }
        return self::createJsonData(true,$list);
    }

    protected function checkUserInfoPercent($user){
        //字段完成度为90%才能创建问题
        $percent = $user->getInfoCompletePercent();
        if($percent < 90){
            throw new ApiException(ApiException::ASK_NEED_USER_INFORMATION);
        }
    }

}
