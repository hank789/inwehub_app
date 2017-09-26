<?php

namespace App\Api\Controllers;

use App\Models\Answer;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Support;
use App\Models\UserTag;
use Illuminate\Http\Request;

class SupportController extends Controller
{

    /**
     * 创建支持记录
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type,$source_id,Request $request)
    {
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


        /*再次点赞相当于是取消点赞*/
        $support = Support::where("user_id",'=',$loginUser->id)->where('supportable_type','=',get_class($source))->where('supportable_id','=',$source_id)->first();
        if($support){
            $support->delete();
            $source->decrement('supports');
            return self::createJsonData(true,['tip'=>'取消点赞成功','type'=>'unsupport']);
        }

        $data = [
            'user_id'        => $loginUser->id,
            'supportable_id'   => $source_id,
            'supportable_type' => get_class($source),
        ];

        $support = Support::create($data);

        if($support){
            $source->increment('supports');
            if($source_type=='answer'){
                UserTag::multiIncrement($source->user_id,$source->question->tags()->get(),'supports');
            }else if($source_type=='article'){
                UserTag::multiIncrement($source->user_id,$source->tags()->get(),'supports');
            }
        }

        return self::createJsonData(true,['tip'=>'点赞成功','type'=>'support']);
    }

}
