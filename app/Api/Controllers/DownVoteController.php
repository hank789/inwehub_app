<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Article;
use App\Models\Comment;
use App\Models\DownVote;
use App\Models\Support;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

class DownVoteController extends Controller
{

    /**
     * 创建踩记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type,Request $request)
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

        $loginUser = $request->user();

        if (RateLimiter::instance()->increase('downvote:'.$source_type,$source_id.'_'.$loginUser->id,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $support = Support::where("user_id",'=',$loginUser->id)->where('supportable_type','=',get_class($source))->where('supportable_id','=',$source_id)->first();
        if ($support) {
            throw new ApiException(ApiException::USER_DOWNVOTE_ALREADY_SUPPORT);
        }

        /*再次踩相当于是取消踩*/
        $downvote = DownVote::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($source))->where('source_id','=',$source_id)->first();
        if($downvote){
            $downvote->delete();
            $source->decrement('downvotes');
            return self::createJsonData(true,['tip'=>'取消踩成功','type'=>'cancel_downvote',
                'support_description'=>$source->getSupportRateDesc(),
                'support_percent'=>$source->getSupportPercent()],ApiException::SUCCESS,'取消踩成功');
        }

        $data = [
            'user_id'        => $loginUser->id,
            'source_id'   => $source_id,
            'source_type' => get_class($source),
            'refer_user_id'    => $source->user_id
        ];

        $downvote = DownVote::create($data);

        if($downvote){
            $source->increment('downvotes');
        }
        return self::createJsonData(true,['tip'=>'踩成功','type'=>'downvote','support_description'=>$source->getSupportRateDesc(),
            'support_percent'=>$source->getSupportPercent()],ApiException::SUCCESS,'踩成功');
    }

}
