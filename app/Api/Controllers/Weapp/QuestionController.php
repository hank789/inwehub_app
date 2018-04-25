<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\TaskLogic;
use App\Models\Credit;
use App\Models\Question;
use App\Models\Tag;
use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:31
 * @email: wanghui@yonglibao.com
 */

class QuestionController extends Controller {

    public function store(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'title' => 'required|max:500',
            'hide'=> 'required'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        \Log::info('data',$request->all());
        $data = [
            'user_id' => $user->id,
            'category_id' => 20,
            'title' => $request->input('title'),
            'question_type' => $request->input('question_type',2),
            'price' => abs($request->input('price')),
            'hide' => $request->input('hide')?1:0,
            'status'    => 1,
            'device'       => intval($request->input('device')),
            'rate'          => firstRate(),
            'data'          => []
        ];
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'questions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $data['data']['img'] = [$img_url];
            }
        }
        $question = Question::create($data);

        /*添加标签*/
        $tagString = $request->input('tags');
        Tag::multiSaveByIds($tagString,$question);
        //记录动态
        $this->doing($question->user_id,'free_question_submit',get_class($question),$question->id,$question->title,'');
        $user->userData()->increment('questions');
        UserTag::multiIncrement($user->id,$question->tags()->get(),'questions');
        //匿名互动提问的不加分
        //首次提问
        if($user->userData->questions == 1){
            if ($question->question_type == 1) {
                $credit_key = Credit::KEY_FIRST_ASK;
            } else {
                $credit_key = Credit::KEY_FIRST_COMMUNITY_ASK;
            }
            TaskLogic::finishTask('newbie_ask',0,'newbie_ask',[$user->id]);
        } else {
            if ($question->question_type == 1) {
                $credit_key = Credit::KEY_ASK;
            } else {
                $credit_key = Credit::KEY_COMMUNITY_ASK;
            }
        }
        if ($question->question_type == 1 || ($question->question_type == 2 && $question->hide==0)) {
            $this->credit($user->id,$credit_key,$question->id,$question->title);
        }
        return self::createJsonData(true,['id'=>$question->id]);
    }

    public function addImage(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id' => 'required|integer',
            'image_file'=> 'required|image'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $data = $request->all();
        $question = Question::find($data['id']);
        if ($question->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $data = $question->data;
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'questions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $data['img'][] = $img_url;
            }
        }
        $question->data = $data;
        $question->save();
        return self::createJsonData(true,['id'=>$question->id]);
    }

    public function myList(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $user = $request->user();

        $query = Question::where('user_id',$user->id)->where('status',1);

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $questions = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));

        $list = [];
        foreach($questions as $question){
            $list[] = [
                'id' => $question->id,
                'user_id' => $question->user_id,
                'user_name' => $question->user->name,
                'user_avatar_url' => $question->user->getAvatarUrl(),
                'description'  => $question->title,
                'status' => $question->status,
                'comments' => $question->comments,
                'created_at' => (string)$question->created_at
            ];
        }
        return self::createJsonData(true,$list);
    }

    public function allList(Request $request){
        $orderBy = $request->input('order_by',2);//1最新，2最热，3综合，
        $query = Question::Where('question_type',2);
        $queryOrderBy = 'questions.rate';
        switch ($orderBy) {
            case 1:
                //最新
                $queryOrderBy = 'questions.updated_at';
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
        $return = $questions->toArray();
        $list = [];
        foreach($questions as $question){
            $item = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'description'  => $question->title,
                'tags' => $question->tags()->get()->toArray(),
                'question_user_name' => $question->hide ? '匿名' : $question->user->name,
                'question_user_avatar' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                'question_user_is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0)
            ];
            if($question->question_type == 1){
                $item['comment_number'] = 0;
                $item['average_rate'] = 0;
                $item['support_number'] = 0;
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
                if ($bestAnswer) {
                    $item['comment_number'] = $bestAnswer->comments;
                    $item['average_rate'] = $bestAnswer->getFeedbackRate();
                    $item['support_number'] = $bestAnswer->supports;
                }
            } else {
                $item['answer_number'] = $question->answers;
                $item['follow_number'] = $question->followers;
            }
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    public function loadAnswer(Request $request){
        $validateRules = [
            'question_id' => 'required|integer'
        ];
        $this->validate($request,$validateRules);

        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $question_id = $request->input('question_id',0);
        $question = Question::find($question_id);
        $user = $request->user();

        $query = $question->comments()->where('status',1);
        if (!$question->is_public && $user->id != $question->user_id){
            $query = $query->where('user_id',$user->id);
        }

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $comments = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));

        $list = [];
        foreach($comments as $comment){
            $list[] = [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_avatar_url' => $comment->user->getAvatarUrl(),
                'user_id'   => $comment->user_id,
                'user_name' => $comment->user->name,
                'created_at' => (string)$comment->created_at
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
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }
        $answers = $question->answers()->whereNull('adopted_at')->orderBy('supports','DESC')->orderBy('updated_at','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
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
                'content' => $answer->getContentHtml(),
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
}