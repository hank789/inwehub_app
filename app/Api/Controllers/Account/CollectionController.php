<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Models\Answer;
use App\Models\Collection;
use App\Models\Question;
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


    public function collectList($source_type,Request $request){
        $sourceClassMap = [
            'questions' => 'App\Models\Question',
            'answers' => 'App\Models\Answer',
        ];

        if(!isset($sourceClassMap[$source_type])){
            abort(404);
        }

        $model = App::make($sourceClassMap[$source_type]);
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = $request->user()->collections()->where('source_type','=',$sourceClassMap[$source_type]);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }

        $attentions = $query->orderBy('collections.created_at','desc')->paginate(10);

        $data = [];
        foreach($attentions as $attention){
            $info = $model::find($attention->source_id);
            $item = [];
            $item['id'] = $attention->id;
            switch($source_type){
                case 'answers':
                    $item['answer_id'] = $info->id;
                    $item['user_name'] = $info->user->name;
                    $item['user_avatar_url'] = $info->user->avatar;
                    $item['is_expert'] = $info->user->userData->authentication_status == 1 ? 1 : 0;
                    $item['title'] = ($info->question->question_type == 1 ? '专业问答|':'互动问答|').$info->question->title;
                    $item['description'] = $info->getContentText();
                    break;
                case 'questions':
                    break;
            }
            $data[] = $item;
        }
        return self::createJsonData(true,$data);
    }

}
