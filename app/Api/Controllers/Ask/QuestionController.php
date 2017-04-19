<?php namespace App\Api\Controllers\Ask;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Tag;
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
        'description' => 'required|max:500',
        'price'=> 'required|digits_between:1,388',
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
            abort(404);
        }

        /*已解决问题*/
        $bestAnswers = [];
        if($question->status >= 6 ){
            $bestAnswers = $question->answers()->where('adopted_at','>',0)->get();
        }

        /*设置通知为已读*/
        if($request->user()){
            $this->readNotifications($question->id,'question');
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
            'status_description' => $question->statusHumanDescription(),
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
                'created_at' => (string)$bestAnswer->created_at
            ];
        }

        $timeline = $question->formatTimeline();

        return self::createJsonData(true,['question'=>$question_data,'answers'=>$answers_data,'timeline'=>$timeline]);

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
        //todo 字段完成度为95%才能创建问题
        if(empty($user->title)){
            throw new ApiException(ApiException::ASK_NEED_USER_INFORMATION);
        }
        $question_c = Category::where('slug','question')->first();
        $question_c_arr = Category::where('parent_id',$question_c->id)->where('status',1)->get();
        $tags = [];
        foreach($question_c_arr as $category){
            $tags[$category->name] = $category->tags()->pluck('name');
        }

        return self::createJsonData(true,['tags'=>$tags]);
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
        $price = abs($request->input('price'));

        $data = [
            'user_id'      => $loginUser->id,
            'category_id'      => $request->input('category_id',0),
            'title'        => trim($request->input('description')),
            'price'        => $price,
            'hide'         => intval($request->input('hide')),
            'status'       => 1,
        ];

        $question = Question::create($data);
        /*判断问题是否添加成功*/
        if($question){

            /*添加标签*/
            $tagString = trim($request->input('tags'));
            Tag::multiSave($tagString,$question);


            //记录动态
            $this->doing($question->user_id,'ask',get_class($question),$question->id,$question->title,'');

            $doing_obj = $this->doing(0,'process',get_class($question),$question->id,$question->title,'');
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
            abort(404);
        }

        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->where('status',0)->first();
        if(empty($question_invitation)){
            abort(404);
        }

        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $request->input('question_id'),
            'content'  => $request->input('description'),
            'status'   => 2,
        ];

        $answer = Answer::create($data);

        //问题已拒绝
        $question->rejectAnswer();

        /*添加标签*/
        $tagString = trim($request->input('tags'));
        Tag::multiSave($tagString,$answer);
        /*记录动态*/
        $this->doing($answer->user_id,'reject_answer',get_class($question),$question->id,$question->title,$answer->content);
        /*修改问题邀请表的回答状态*/
        QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$request->user()->id)->update(['status'=>2]);

        return self::createJsonData(true,['question_id'=>$data['question_id'],'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
    }

    //我的提问列表
    public function myList(Request $request){
        $questions = $request->user()->questions()->orderBy('created_at','DESC')->paginate(10);

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
                'status_description' => $question->statusHumanDescription(),
                'created_at' => (string)$question->created_at,
                'answer_user_id' => $bestAnswer ? $bestAnswer->user->id : '',
                'answer_username' => $bestAnswer ? $bestAnswer->user->name : '',
                'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->getAvatarUrl() : '',
                'answer_time' => $bestAnswer ? $bestAnswer->created_at : ''
            ];
        }
        return self::createJsonData(true,$list);
    }




    /*邀请回答*/
    public function invite($question_id,$to_user_id,Request $request){

        $loginUser = $request->user();

        if($loginUser->id == $to_user_id){
            return $this->ajaxError(50009,'不用邀请自己，您可以直接回答 ：）');
        }

        $question = Question::find($question_id);
        if(!$question){
           return $this->ajaxError(50001,'notFound');
        }

        if( $this->counter('question_invite_num_'.$loginUser->id) > config('intervapp.user_invite_limit') ){
            return $this->ajaxError(50007,'超出每天最大邀请次数');
        }


        $toUser = User::find(intval($to_user_id));
        if(!$toUser){
            return $this->ajaxError(50005,'被邀请用户不存在');
        }

        if(!$toUser->allowedEmailNotify('invite_answer')){
            return $this->ajaxError(50006,'邀请人设置为不允许被邀请回答');
        }

        /*是否已邀请，不能重复邀请*/
        if($question->isInvited($toUser->email,$loginUser->id)){
            return $this->ajaxError(50008,'该用户已被邀请，不能重复邀请');
        }

        $invitation = QuestionInvitation::create([
            'from_user_id'=> $loginUser->id,
            'question_id'=> $question->id,
            'user_id'=> $toUser->id,
            'send_to'=> $toUser->email
        ]);

        if($invitation){
            $this->counter('question_invite_num_'.$loginUser->id);
            $subject = $loginUser->name."在「".Setting()->get('website_name')."」向您发起了回答邀请";
            $message = "我在 ".Setting()->get('website_name')." 上遇到了问题「".$question->title."」 → ".route("ask.question.detail",['question_id'=>$question->id])."，希望您能帮我解答 ";
            $this->sendEmail($invitation->send_to,$subject,$message);
            return $this->ajaxSuccess('success');
        }

        return $this->ajaxError(10008,'邀请失败，请稍后再试');
    }


    public function inviteEmail($question_id,Request $request){

        $loginUser = $request->user();

        if( $this->counter('question_invite_num_'.$loginUser->id) > config('intervapp.user_invite_limit') ){
            return $this->ajaxError(50007,'超出每天最大邀请次数');
        }

        $question = Question::find($question_id);
        if(!$question){
            return $this->ajaxError(50001,'question not fund');
        }

        $validator = Validator::make($request->all(), [
            'sendTo' =>  'required|email|max:255',
            'message' =>'required|min:10|max:10000',
        ]);

        if($validator->fails()){
            $this->ajaxError(50011,'字段校验失败');
        }

        $loginUser = $request->user();
        $email    = $request->input('sendTo');
        $content = $request->input('message');
        /*是否已邀请，不能重复邀请*/
        if($question->isInvited($email,$loginUser->id)){
            return $this->ajaxError(50008,'该用户已被邀请，不能重复邀请');
        }

        $invitation = QuestionInvitation::create([
            'from_user_id'=> $loginUser->id,
            'question_id'=> $question->id,
            'user_id'=> 0,
            'send_to'=> $email
        ]);

        if($invitation){
            $this->counter('question_invite_num_'.$loginUser->id,1);
            $subject = $loginUser->name."在「".Setting()->get('website_name')."」向您发起了回答邀请";
            $message = $content;
            $this->sendEmail($invitation->send_to,$subject,$message);
            return $this->ajaxSuccess('success');
        }

        return $this->ajaxError(10008,'邀请失败，请稍后再试');
    }

    public function invitations($question_id,$type){
        $question = Question::find($question_id);
        if(!$question){
            return $this->ajaxError(50001,'question not fund');
        }

        $showRows = ($type=='part') ? 3:100;

        $invitations = $question->invitations()->where("user_id",">",0)->orderBy('created_at','desc')->groupBy('user_id')->take($showRows);

        $invitedUsers = [];
        foreach( $invitations->get() as $invitation ){
            if($invitation->user()){
                $invitedUsers[] = '<a target="_blank" href="'.route('auth.space.index',['user_id'=>$invitation->user->id]).'">'.$invitation->user->name.'</a>';
            }
        }

        $invitedHtml = implode(",&nbsp;",$invitedUsers);

        $totalInvitedNum = $invitations->count();
        if( $type == 'part' &&  $totalInvitedNum > $showRows ){
            $invitedHtml .= '等 <a id="showAllInvitedUsers" href="javascript:void(0);">'.$totalInvitedNum.'</a> 人';
        }
        if( $totalInvitedNum > 0 ){
            $invitedHtml .= '&nbsp;已被邀请';
        }

        return response($invitedHtml);

    }



}
