<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Answer;
use App\Models\Collection;
use App\Models\Groups\Group;
use App\Models\Question;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

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
            $question = $source;
            $subject = $source->title;
        }else if($source_type === 'answer'){
            $source  = Answer::findOrFail($source_id);
            $question = $source->question;
            $subject = '';
        }
        $user = $request->user();

        if (RateLimiter::instance()->increase('collect:'.$source_type,$user->id,10,5)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        /*不能多次收藏*/
        $userCollect = $user->isCollected(get_class($source),$source_id);
        if($userCollect){
            $userCollect->delete();
            $source->decrement('collections');
            UserTag::multiDecrement($user->id,$question->tags()->get(),'questions');
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
            UserTag::multiIncrement($user->id,$question->tags()->get(),'questions');
        }

        return self::createJsonData(true,['tip'=>'收藏成功','type'=>'collect']);
    }


    public function collectList($source_type,Request $request){
        $sourceClassMap = [
            'questions' => 'App\Models\Question',
            'answers' => 'App\Models\Answer',
            'readhubSubmission' => 'App\Models\Submission',
            'reviews' => 'App\Models\Submission'
        ];

        if(!isset($sourceClassMap[$source_type])){
            abort(404);
        }
        $requestData = $request->all();
        $model = App::make($sourceClassMap[$source_type]);
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);
        $user = $request->user();


        $query = $user->collections()->where('source_type','=',$sourceClassMap[$source_type]);

        if ($source_type == 'readhubSubmission') {
            $query = $query->where('status',1);
        } elseif ($source_type == 'reviews') {
            $query = $query->where('status',2);
        }

        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }

        $attentions = $query->orderBy('id','desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $attentions->toArray();
        $data = [];
        foreach($attentions as $attention){
            $item = [];
            $item['id'] = $attention->id;
            switch($source_type){
                case 'answers':
                    $info = $model::find($attention->source_id);
                    $item['answer_id'] = $info->id;
                    $item['question_id'] = $info->question_id;
                    $item['question_type'] = $info->question->question_type;
                    $item['user_name'] = $info->user->name;
                    $item['user_avatar_url'] = $info->user->avatar;
                    $item['is_expert'] = $info->user->userData->authentication_status == 1 ? 1 : 0;
                    $item['title'] = ('问答|').$info->question->title;
                    $item['description'] = $info->getContentText();
                    break;
                case 'questions':
                    break;
                case 'readhubSubmission':
                    $submission = $model::find($attention->source_id);
                    $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                    $group = Group::find($submission->group_id);
                    $item = [
                        'id' => $attention->id,
                        'type' => $submission->type,
                        'title' => $submission->formatTitle(),
                        'img'   => $submission->data['img']??[],
                        'slug'  => $submission->slug,
                        'submission_url' => $submission->data['url']??$comment_url,
                        'comment_url'    => $comment_url,
                        'domain'         => $submission->data['domain']??$submission->user->name,
                        'category_name'  => $group?$group->name:'',
                        'created_at'     => (string) $submission->created_at
                    ];
                    if (!is_array($item['img'])) {
                        $item['img'] = [$item['img']];
                    }
                    break;
                case 'reviews':
                    $submission = $model::find($attention->source_id);
                    $item = $submission->formatListItem($user, false);
                    break;
            }
            $data[] = $item;
        }
        $return['data'] = $data;
        if (isset($requestData['bottom_id'])) {
            return self::createJsonData(true,$data);
        } else {
            return self::createJsonData(true,$return);
        }
    }

}
