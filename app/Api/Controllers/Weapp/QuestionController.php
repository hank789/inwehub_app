<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Comment;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:31
 * @email: wanghui@yonglibao.com
 */

class QuestionController extends Controller {

    public function store(Request $request){
        $validateRules = [
            'description' => 'required|max:500',
            'is_public'=> 'required'
        ];
        $this->validate($request,$validateRules);

        $data = $request->all();
        $question = Question::create([
            'user_id' => $request->user()->id,
            'title' => $data['description'],
            'is_public' => $data['is_public']?1:0,
            'status'    => 1
        ]);
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $question->addMediaFromRequest($image_file)->setFileName(time().'_'.md5($file_0->getFilename()).'.'.$extension)->toMediaCollection('weapp');
            }
        }
        \Log::info('test',$request->all());
        return self::createJsonData(true,['id'=>$question->id]);
    }

    public function addImage(Request $request){
        $validateRules = [
            'id' => 'required|integer',
            'image_file'=> 'required|image'
        ];
        $this->validate($request,$validateRules);

        $data = $request->all();
        $question = Question::find($data['id']);
        if ($question->user_id != $request->user()->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $question->addMediaFromRequest($image_file)->setFileName(time().'_'.md5($file_0->getFilename()).'.'.$extension)->toMediaCollection('weapp');
            }
        }
        \Log::info('test',$request->all());
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

}