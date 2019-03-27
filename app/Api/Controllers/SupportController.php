<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Article;
use App\Models\Comment;
use App\Models\DownVote;
use App\Models\Support;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class SupportController extends Controller
{

    /**
     * 创建支持记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type,Request $request, JWTAuth $JWTAuth)
    {
        $validateRules = [
            'id' => 'required|integer'
        ];
        $this->validate($request, $validateRules);
        $source_id = $request->input('id');

        if($source_type === 'answer'){
            $source  = Answer::find($source_id);
        }elseif($source_type === 'article'){
            $source  = Article::find($source_id);
        }elseif($source_type === 'comment'){
            $source  = Comment::find($source_id);
        }

        if(!$source){
            abort(404);
        }
        if ($request->input('inwehub_user_device') == 'weapp_dianping') {
            $oauth = $JWTAuth->parseToken()->toUser();
            if ($oauth->user_id) {
                $loginUser = $oauth->user;
            } else {
                throw new ApiException(ApiException::USER_WEIXIN_NEED_REGISTER);
            }
            if (empty($loginUser->mobile)) {
                throw new ApiException(ApiException::USER_NEED_VALID_PHONE);
            }
        } else {
            $loginUser = $request->user();
        }

        if (RateLimiter::instance()->increase('support:'.$source_type,$source_id.'_'.$loginUser->id,1,2)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        //已经踩过，不能点赞
        $downvote = DownVote::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($source))->where('source_id','=',$source_id)->first();
        if ($downvote) {
            throw new ApiException(ApiException::USER_SUPPORT_ALREADY_DOWNVOTE);
        }


        /*再次点赞相当于是取消点赞*/
        $support = Support::where("user_id",'=',$loginUser->id)->where('supportable_type','=',get_class($source))->where('supportable_id','=',$source_id)->first();
        if($support){
            $support->delete();
            $source->decrement('supports');
            return self::createJsonData(true,['tip'=>'取消点赞成功','type'=>'unsupport','support_description'=>$source->getSupportRateDesc(),
                'support_percent'=>$source->getSupportPercent()]);
        }

        $data = [
            'user_id'        => $loginUser->id,
            'supportable_id'   => $source_id,
            'supportable_type' => get_class($source),
            'refer_user_id'    => $source->user_id
        ];

        $support = Support::create($data);

        if($support){
            $source->increment('supports');
            if($source_type=='answer'){
                UserTag::multiIncrement($source->user_id,$source->question->tags()->get(),'questions');
            }else if($source_type=='article'){
                UserTag::multiIncrement($source->user_id,$source->tags()->get(),'supports');
            }
        }

        return self::createJsonData(true,['tip'=>'点赞成功','type'=>'support','support_description'=>$source->getSupportRateDesc(),
            'support_percent'=>$source->getSupportPercent()]);
    }

}
