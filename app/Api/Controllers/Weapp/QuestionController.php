<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Comment;
use App\Models\WeappQuestion\WeappQuestion;
use Illuminate\Http\Request;

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
        $question = WeappQuestion::create([
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
        $question = WeappQuestion::find($data['id']);
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

        $query = WeappQuestion::where('user_id',$user->id)->where('status',1);

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

    }

    public function info(Request $request){
        $validateRules = [
            'id' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();
        $user = $request->user();
        $question = WeappQuestion::find($data['id']);
        $info = [
            'id' => $question->id,
            'user_id' => $question->user_id,
            'user_name' => $question->user->name,
            'user_avatar_url' => $question->user->getAvatarUrl(),
            'description'  => $question->title,
            'status' => $question->status,
            'comments' => $question->comments,
            'created_at' => (string)$question->created_at
        ];
        $comments_query = $question->comments()->where('status',1);
        if (!$question->is_public && $user->id != $question->user_id){
            $comments_query = $comments_query->where('user_id',$user->id);
        }

        $res_data = [];
        $res_data['question'] = $info;
        $res_data['comments_count'] = $comments_query->orderBy('created_at','desc')->count();

        $res_data['images'] = [];
        if(!$question->getMedia('weapp')->isEmpty()){
            foreach($question->getMedia('weapp') as $image){
                $res_data['images'][] = $image->getUrl();
            }
        }

        return self::createJsonData(true,$res_data);
    }

    public function loadAnswer(Request $request){
        $validateRules = [
            'description' => 'question_id|integer'
        ];
        $this->validate($request,$validateRules);

        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $question_id = $request->input('question_id',0);
        $question = WeappQuestion::find($question_id);
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
        $comments = $query->orderBy('id','DESC')->paginate(10);

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