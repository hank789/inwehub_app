<?php namespace App\Api\Controllers\Account;

use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Models\Attention;
use App\Models\Feed\Feed;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\NewUserFollowing;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;


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
        $loginUser = $request->user();

        if($source_type === 'question'){
            $source  = Question::findOrFail($source_id);
            $subject = $source->title;
        }else if($source_type === 'user'){
            $source = User::where('uuid',$source_id)->first();
            if(empty($source)){
                $source  = User::findOrFail($source_id);
            }
            $source_id = $source->id;
            if($source_id == $loginUser->id){
                throw new ApiException(ApiException::USER_CANNOT_FOLLOWED_SELF);
            }
            $subject = $source->name;
        }else if($source_type==='tag'){
            $source  = Tag::findOrFail($source_id);
            $subject = $source->name;
        }

        /*再次关注相当于是取消关注*/
        $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($source))->where('source_id','=',$source_id)->first();
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
            'user_id'     => $loginUser->id,
            'source_id'   => $source_id,
            'source_type' => get_class($source),
        ];

        $attention = Attention::create($data);

        if($attention){
            switch($source_type){
                case 'question' :
                    $source->increment('followers');
                    $fields = [];
                    $fields[] = [
                        'title' => '标题',
                        'value' => $source->title
                    ];
                    $fields[] = [
                        'title' => '地址',
                        'value' => route('ask.question.detail',['id'=>$source->id])
                    ];
                    event(new SystemNotify($loginUser->name.'关注了问题',$fields));
                    //产生一条feed流
                    if ($source->question_type == 2) {
                        $feed_event = 'question_followed';
                        $feed_target = $source->id.'_'.$loginUser->id;
                        $is_feeded = RateLimiter::instance()->getValue($feed_event,$feed_target);
                        if (!$is_feeded) {
                            feed()
                                ->causedBy($loginUser)
                                ->performedOn($source)
                                ->withProperties(['question_id'=>$source->id,'question_title'=>$source->title])
                                ->log($loginUser->name.'关注了互动问答', Feed::FEED_TYPE_FOLLOW_FREE_QUESTION);
                            RateLimiter::instance()->increase($feed_event,$feed_target,3600);
                        }
                    }
                    break;
                case 'user':
                    $source->userData->increment('followers');
                    $source->notify(new NewUserFollowing($source->id,$attention));
                    //产生一条feed流
                    $feed_event = 'user_followed';
                    $feed_target = $source->id.'_'.$loginUser->id;
                    $is_feeded = RateLimiter::instance()->getValue($feed_event,$feed_target);
                    if (!$is_feeded) {
                        feed()
                            ->causedBy($loginUser)
                            ->performedOn($source)
                            ->withProperties([
                                'follow_user_id' => $source->id
                            ])
                            ->log($loginUser->name.'关注了新的朋友', Feed::FEED_TYPE_FOLLOW_USER);
                        RateLimiter::instance()->increase($feed_event,$feed_target,3600);
                    }
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

        $attentions = $query->orderBy('attentions.created_at','desc')->paginate(30);

        $data = [];
        foreach($attentions as $attention){
            $info = $model::find($attention->source_id);
            $item = [];
            $item['id'] = $attention->id;
            switch($source_type){
                case 'users':
                    $item['user_id'] = $info->id;
                    $item['uuid'] = $info->uuid;
                    $item['user_name'] = $info->name;
                    $item['company'] = $info->company;
                    $item['title'] = $info->title;
                    $item['user_avatar_url'] = $info->avatar;
                    $item['is_expert'] = ($info->authentication && $info->authentication->status == 1) ? 1 : 0;
                    $item['description'] = $info->description;
                    $item['is_followed'] = 1;
                    break;
                case 'questions':
                    $item['question_id'] = $info->id;
                    $item['question_type'] = $info->question_type;
                    $item['user_name'] = $info->hide ? '匿名' : $info->user->name;
                    $item['user_avatar_url'] = $info->hide ? config('image.user_default_avatar') : $info->user->getAvatarUrl();
                    $item['description'] = $info->title;
                    $item['answer_num'] = $info->answers;
                    $item['follow_num'] = $info->followers;
                    $item['is_followed'] = 1;
                    break;
            }
            $data[] = $item;
        }
        return self::createJsonData(true,$data);
    }


    /*关注我的用户*/
    public function followMe(Request $request)
    {

        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $user = $request->user();
        $query = Attention::where('source_type','=','App\Models\User')->where('source_id',$user->id);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }

        $attentions = $query->orderBy('created_at','desc')->paginate(Config::get('api_data_page_size'));

        $data = [];
        foreach($attentions as $attention){
            $info = User::find($attention->user_id);
            $item = [];
            $item['id'] = $attention->id;
            $item['user_id'] = $info->id;
            $item['uuid'] = $info->uuid;
            $item['company'] = $info->company;
            $item['title'] = $info->title;
            $item['is_expert'] = ($info->authentication && $info->authentication->status === 1) ? 1 : 0;
            $item['user_name'] = $info->name;
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($info))->where('source_id','=',$info->id)->first();
            $item['is_following'] = 0;
            if ($attention){
                $item['is_following'] = 1;
            }
            $item['user_avatar_url'] = $info->getAvatarUrl();
            $item['description'] = $info->description;
            $data[] = $item;
        }
        return self::createJsonData(true,$data);
    }


    //搜索我关注的用户
    public function searchFollowedUser(Request $request) {
        $name = $request->input('name');
        $query = $request->user()->attentions()->where('source_type','=','App\Models\User')
            ->leftJoin('users','attentions.source_id','=','users.id');
        if ($name) {
            $query = $query->where('users.name','like',$name.'%');
        }
        $users = $query->select('users.*','attentions.id as attention_id')->get();
        $data = [];
        foreach ($users as $user) {
            $item = [];
            $item['id'] = $user->attention_id;
            $item['user_id'] = $user->id;
            $item['uuid'] = $user->uuid;
            $item['user_name'] = $user->name;
            $item['company'] = $user->company;
            $item['title'] = $user->title;
            $item['user_avatar_url'] = $user->avatar;
            $item['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
            $item['description'] = $user->description;
            $item['is_followed'] = 1;
            $data[] = $item;
        }

        return self::createJsonData(true,$data);

    }

}
