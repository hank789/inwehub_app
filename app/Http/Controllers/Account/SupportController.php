<?php

namespace App\Http\Controllers\Account;

use App\Models\Answer;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Support;
use App\Models\UserTag;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
        $user_id = $request->user()->id;



        /*再次关注相当于是取消关注*/
        $support = Support::where("user_id",'=',$user_id)->where('supportable_type','=',get_class($source))->where('supportable_id','=',$source_id)->first();
        if($support){
            return response('supported');
        }

        $data = [
            'session_id'     => $request->session()->getId(),
            'user_id'        => $user_id,
            'supportable_id'   => $source_id,
            'supportable_type' => get_class($source),
        ];

        $support = Support::create($data);

        if($support){
            $source->increment('supports');
            $source->user->userData->increment('supports');
            if($source_type=='answer'){
                UserTag::multiIncrement($source->user_id,$source->question->tags()->get(),'supports');
            }else if($source_type=='article'){
                UserTag::multiIncrement($source->user_id,$source->tags()->get(),'supports');
            }
        }

        return response('success');
    }


    public function check($source_type,$source_id,Request $request)
    {
        if($source_type === 'answer'){
            $source  = Answer::find($source_id);
        }

        if(!$source){
            abort(404);
        }


        /*再次关注相当于是取消关注*/
        $support = Support::where('supportable_type','=',get_class($source))->where('supportable_id','=',$source_id)->first();
        if($support){
            return response('failed');
        }

        return response('success');

    }

}
