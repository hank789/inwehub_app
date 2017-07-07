<?php namespace App\Api\Controllers\Account;

use App\Exceptions\ApiException;
use App\Models\Attention;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\App;


class FollowController extends Controller
{

    /**
     * 添加模型的关注包含问题、用户等
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($source_type, Request $request)
    {

        $validateRules = [
            'id' => 'required',
        ];

        $this->validate($request,$validateRules);

        $source_id = $request->input('id');

        if($source_type === 'question'){
            $source  = Question::findOrFail($source_id);
            $subject = $source->title;
        }else if($source_type === 'user'){
            $source = User::where('uuid',$source_id)->first();
            if(empty($source)){
                $source  = User::findOrFail($source_id);
            }
            $source_id = $source->id;
            $subject = $source->name;
        }else if($source_type==='tag'){
            $source  = Tag::findOrFail($source_id);
            $subject = $source->name;
        }

        /*再次关注相当于是取消关注*/
        $attention = Attention::where("user_id",'=',$request->user()->id)->where('source_type','=',get_class($source))->where('source_id','=',$source_id)->first();
        if($attention){
            $attention->delete();
            if($source_type==='user'){
                $source->userData->decrement('followers');
            }else{
                $source->decrement('followers');
            }
            return self::createJsonData(true,['tip'=>'取消关注成功','type'=>'unfollow']);
        }

        $data = [
            'user_id'     => $request->user()->id,
            'source_id'   => $source_id,
            'source_type' => get_class($source),
        ];

        $attention = Attention::create($data);

        if($attention){
            switch($source_type){
                case 'question' :
                    $this->notify($request->user()->id,$source->user_id,'follow_question',$subject,$source->id);
                    $this->doing($request->user()->id,'follow_question',get_class($source),$source_id,$subject);
                    $source->increment('followers');
                    break;
                case 'user':
                    $source->userData->increment('followers');
                    $this->notify($request->user()->id,$source->id,'follow_user');
                    break;
                case 'tag':
                    $source->increment('followers');
                    break;
            }
        }

        return self::createJsonData(true,['tip'=>'关注成功','type'=>'follow']);

    }

    /*我的关注*/
    public function attentions(Request $request)
    {
        $source_type = $request->route()->parameter('source_type');
        $sourceClassMap = [
            'questions' => 'App\Models\Question',
            'users' => 'App\Models\User',
            'tags' => 'App\Models\Tag',
        ];

        if(!isset($sourceClassMap[$source_type])){
            abort(404);
        }

        $model = App::make($sourceClassMap[$source_type]);
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = $request->user()->attentions()->where('source_type','=',$sourceClassMap[$source_type]);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }

        $attentions = $query->orderBy('attentions.created_at','desc')->paginate(10);

        $data = [];
        foreach($attentions as $attention){
            $info = $model::find($attention->source_id);
            $item = [];
            $item['id'] = $attention->id;
            switch($source_type){
                case 'users':
                    $item['user_id'] = $info->id;
                    $item['user_name'] = $info->name;
                    $item['user_avatar_url'] = $info->getAvatarUrl();
                    $item['description'] = $info->description;
                    break;
                case 'question':
                    $item['question_id'] = $info->id;
                    $item['user_name'] = $info->hide ? '匿名' : $info->user->name;
                    $item['user_avatar_url'] = $info->hide ? config('image.user_default_avatar') : $info->user->getAvatarUrl();
                    $item['description'] = $info->title;
                    break;
            }
            $data[] = $item;
        }
        return self::createJsonData(true,$data);
    }




}
