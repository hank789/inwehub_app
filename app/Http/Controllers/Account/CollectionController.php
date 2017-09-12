<?php

namespace App\Http\Controllers\Account;

use App\Models\Article;
use App\Models\Collection;
use App\Models\Question;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CollectionController extends Controller
{

    /**
     * 添加收藏
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type,$source_id,Request $request)
    {

        if($source_type === 'question'){
            $source  = Question::find($source_id);
            $subject = $source->title;
        }else if($source_type === 'article'){
            $source  = Article::find($source_id);
            $subject = $source->title;
        }

        if(!$source){
            abort(404);
        }

        /*不能多次收藏*/
        $userCollect = $request->user()->isCollected(get_class($source),$source_id);
        if($userCollect){
            $userCollect->delete();
            $source->decrement('collections');
            return response('uncollect');
        }

        $data = [
            'user_id'     => $request->user()->id,
            'source_id'   => $source_id,
            'source_type' => get_class($source),
            'subject'  => $subject,
        ];

        $collect = Collection::create($data);

        if($collect){
            $source->increment('collections');
        }

        return response('collected');
    }

    public function verify($source_id,Request $request)
    {
        $source  = Collection::find($source_id);

        if(!$source){
            abort(404);
        }
        $source->status = 2;
        $source->subject = $request->input('message');
        $source->save();

        return response('ok');
    }

    public function unverify(Request $request)
    {
        $source  = Collection::find($request->input('collect_id'));

        if(!$source){
            abort(404);
        }

        $source->status = 3;
        $source->subject = $request->input('message');
        $source->save();

        return $this->success(route('blog.article.detail',['id'=>$source->source_id]),"审核成功");


    }


}
