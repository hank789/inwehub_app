<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Models\Answer;
use App\Models\Article;
use App\Models\Collection;
use App\Models\Question;
use App\Notifications\ActivityEnroll;
use Illuminate\Http\Request;

class CollectionController extends Controller
{

    /**
     * 添加收藏
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type,Request $request)
    {
        $validateRules = [
            'id' => 'required',
        ];

        $this->validate($request,$validateRules);

        $source_id = $request->input('id');

        if($source_type === 'question'){
            $source  = Question::findOrFail($source_id);
            $subject = $source->title;
        }else if($source_type === 'answer'){
            $source  = Answer::findOrFail($source_id);
            $subject = '';
        }

        /*不能多次收藏*/
        $userCollect = $request->user()->isCollected(get_class($source),$source_id);
        if($userCollect){
            $userCollect->delete();
            $source->decrement('collections');
            return self::createJsonData(true,['tip'=>'取消收藏成功','type'=>'uncollect']);
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

        return self::createJsonData(true,['tip'=>'收藏成功','type'=>'collect']);
    }


}
