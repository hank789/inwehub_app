<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Comment;
use App\Models\WeappQuestion\WeappQuestion;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:31
 * @email: wanghui@yonglibao.com
 */

class AnswerController extends Controller {

    public function store(Request $request){
        $validateRules = [
            'description' => 'required|max:500',
            'question_id'=> 'required'
        ];
        $this->validate($request,$validateRules);
        if(RateLimiter::instance()->increase('weapp_answer',$request->user()->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $data = $request->all();

        $question = WeappQuestion::find($data['question_id']);
        $data = [
            'user_id'     => $request->user()->id,
            'content'     => $data['description'],
            'source_id'   => $data['question_id'],
            'source_type' => get_class($question),
            'to_user_id'  => $question->user_id,
            'status'      => 1,
            'supports'    => 0,
            'device'      => 4
        ];
        $comment = Comment::create($data);
        $comment->source()->increment('comments');
        return self::createJsonData(true,['id'=>$comment->id]);

    }

}