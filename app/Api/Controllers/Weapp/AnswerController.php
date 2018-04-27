<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Answer;
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

class AnswerController extends Controller {

    public function store(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'description' => 'required',
            'question_id'=> 'required',
            'device' => 'required'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $description = $request->input('description');
        $quillContent = [];
        $quillContent['ops'][] = [
            'insert' => $description
        ];
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'quill/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $quillContent['ops'][] = [
                    'insert' => [
                        'image' => $img_url
                    ]
                ];
            }
        }
        return $this->storeAnswer($user,json_encode($quillContent),$request);
    }

    public function myList(Request $request,JWTAuth $JWTAuth){
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
        }
        $query = Answer::where('user_id','=',$user->id);
        $answers = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $answers->toArray();
        $list = [];
        foreach($answers as $answer){
            $question = $answer->question;
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
}