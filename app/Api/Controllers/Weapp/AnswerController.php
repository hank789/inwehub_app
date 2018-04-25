<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
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
        return $this->storeAnswer($user,$description,$request);
    }

}