<?php namespace App\Api\Controllers\Account;

use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Logic\QuestionLogic;
use App\Models\AddressBook;
use App\Models\Attention;
use App\Models\Authentication;
use App\Models\Credit;
use App\Models\Feed\Feed;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewUserFollowing;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;


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

        $limit_expire = 10;
        if($source_type === 'question'){
            $source  = Question::findOrFail($source_id);
            $subject = $source->title;
        }else if($source_type === 'user'){
            $source = User::where('uuid','=',$source_id.'')->first();
            if(empty($source)){
                $source  = User::findOrFail($source_id);
            }
            $source_id = $source->id;
            if($source_id == $loginUser->id){
                throw new ApiException(ApiException::USER_CANNOT_FOLLOWED_SELF);
            }
            $subject = $source->name;
            $limit_expire = 5;
        }else if($source_type==='tag'){
            $source  = Tag::findOrFail($source_id);
            $subject = $source->name;
        }

        if (RateLimiter::instance()->increase('follow:'.$source_type,$source_id.'_'.$loginUser->id,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        /*再次关注相当于是取消关注*/
        $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($source))->where('source_id','=',$source_id)->first();
        if($attention){
            $attention->delete();
            if($source_type==='user'){
                $source->userData->decrement('followers');
                event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']取消关注了用户'.$source->id.'['.$source->name.']'));
            }elseif ($source_type == 'question'){
                $source->decrement('followers');
                $fields = [];
                $fields[] = [
                    'title' => '标题',
                    'value' => $source->title
                ];
                $fields[] = [
                    'title' => '地址',
                    'value' => route('ask.question.detail',['id'=>$source->id])
                ];
                QuestionLogic::calculationQuestionRate($source->id);
                UserTag::multiDecrement($loginUser->id,$source->tags()->get(),'questions');
                event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']取消关注了问题',$fields));
            } elseif ($source_type == 'tag') {
                if ($source->followers > 0) {
                    $source->decrement('followers');
                }
                $fields = [];
                $fields[] = [
                    'title' => '标签',
                    'value' => $source->name
                ];
                event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']取消关注了标签',$fields));
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
                    UserTag::multiIncrement($loginUser->id,$source->tags()->get(),'questions');
                    QuestionLogic::calculationQuestionRate($source->id);
                    event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']关注了问题',$fields));
                    //产生一条feed流
                    if ($source->question_type == 2) {
                        $feed_event = 'question_followed';
                        $feed_target = $source->id.'_'.$loginUser->id;
                        if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                            feed()
                                ->causedBy($loginUser)
                                ->performedOn($source)
                                ->log($loginUser->name.'关注了问答', Feed::FEED_TYPE_FOLLOW_FREE_QUESTION);
                            $this->credit($loginUser->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source));
                            if ($source->hide == 0) {
                                $this->credit($source->user_id,Credit::KEY_COMMUNITY_ASK_FOLLOWED,$attention->id,get_class($source));
                            }
                            QuestionLogic::calculationQuestionRate($source->id);
                        }
                    }
                    break;
                case 'user':
                    $source->userData->increment('followers');
                    //产生一条feed流
                    $feed_event = 'user_followed';
                    $feed_target = $source->id.'_'.$loginUser->id;
                    if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                        $source->notify(new NewUserFollowing($source->id,$attention));

                        $this->credit($loginUser->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source));
                        //产生一条私信
                        $message = $loginUser->messages()->create([
                            'data' => ['text'=>'我已经关注你为好友，以后请多多交流~'],
                        ]);
                        $room = Room::where('user_id',$loginUser->id)
                            ->where('source_id',$source->id)
                            ->where('source_type',get_class($loginUser))
                            ->where('r_type',Room::ROOM_TYPE_WHISPER)
                            ->first();
                        if (!$room) {
                            $room = Room::where('user_id',$source->id)
                                ->where('source_id',$loginUser->id)
                                ->where('source_type',get_class($loginUser))
                                ->where('r_type',Room::ROOM_TYPE_WHISPER)
                                ->first();
                        }
                        if ($room) {
                            $room_id = $room->id;
                        } else {
                            $room = Room::create([
                                'user_id' => $loginUser->id,
                                'source_id' => $source->id,
                                'source_type' => get_class($loginUser),
                                'r_name' => '私信',
                                'r_type' => Room::ROOM_TYPE_WHISPER
                            ]);
                            $room_id = $room->id;
                        }
                        $roomUser = RoomUser::firstOrCreate([
                            'user_id' => $loginUser->id,
                            'room_id' => $room_id
                        ],[
                            'user_id' => $loginUser->id,
                            'room_id' => $room_id
                        ]);
                        $roomUser->last_msg_id = $message->id;
                        $roomUser->save();

                        MessageRoom::create([
                            'room_id' => $room_id,
                            'message_id' => $message->id
                        ]);

                        RoomUser::firstOrCreate([
                            'user_id' => $source->id,
                            'room_id' => $room_id
                        ],[
                            'user_id' => $source->id,
                            'room_id' => $room_id
                        ]);
                    }
                    break;
                case 'tag':
                    $source->increment('followers');
                    $fields = [];
                    $fields[] = [
                        'title' => '标签',
                        'value' => $source->name
                    ];
                    event(new SystemNotify('用户'.$loginUser->id.'['.$loginUser->name.']关注了标签',$fields));
                    $feed_event = 'tag_followed';
                    $feed_target = $source->id.'_'.$loginUser->id;
                    if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                        $this->credit($loginUser->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source));
                    }
                    break;
            }
        }

        return self::createJsonData(true,['tip'=>'关注成功','type'=>'follow']);

    }

    //一键关注多个用户
    public function batchUser(Request $request){
        $validateRules = [
            'ids' => 'required|array',
        ];

        $this->validate($request,$validateRules);

        $ids = $request->input('ids');
        $user = $request->user();
        $room_ids = RoomUser::select('room_id')->where('user_id',$user->id)->get()->pluck('room_id')->toArray();
        $fields = [];
        if ($ids) {
            //产生一条私信
            $message = $user->messages()->create([
                'data' => ['text'=>'我已经关注你为好友，以后请多多交流~'],
            ]);
        }
        foreach ($ids as $id) {
            $source = User::where('uuid','=',$id.'')->first();
            if(empty($source)){
                $source  = User::findOrFail($id);
            }
            if ($source->id == $user->id) continue;
            $attention = Attention::where("user_id",'=',$user->id)->where('source_id','=',$id)->where('source_type','=',get_class($source))->first();
            if($attention){
                continue;
            }

            $data = [
                'user_id'     => $user->id,
                'source_id'   => $id,
                'source_type' => get_class($source),
            ];

            $attention = Attention::create($data);

            $source->userData->increment('followers');
            //产生一条feed流
            $feed_event = 'user_followed';
            $feed_target = $source->id.'_'.$user->id;
            if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                $fields[] = [
                    'title' => '用户',
                    'value' => $source->id.'['.$source->name.']'
                ];
                $source->notify(new NewUserFollowing($source->id,$attention,false));

                $this->credit($user->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source),false);

                $roomUser = RoomUser::where('user_id',$source->id)->whereIn('room_id',$room_ids)->first();
                if ($roomUser) {
                    $room_id = $roomUser->room_id;
                } else {
                    $room = Room::create([
                        'user_id' => $user->id,
                        'source_id' => $source->id,
                        'source_type' => get_class($user),
                        'r_name' => '私信',
                        'r_type' => Room::ROOM_TYPE_WHISPER
                    ]);
                    $room_id = $room->id;
                }
                RoomUser::firstOrCreate([
                    'user_id' => $user->id,
                    'room_id' => $room_id
                ],[
                    'user_id' => $user->id,
                    'room_id' => $room_id
                ]);

                MessageRoom::create([
                    'room_id' => $room_id,
                    'message_id' => $message->id
                ]);

                RoomUser::firstOrCreate([
                    'user_id' => $source->id,
                    'room_id' => $room_id
                ],[
                    'user_id' => $source->id,
                    'room_id' => $room_id
                ]);
            }
        }
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']批量关注了用户',$fields));
        return self::createJsonData(true,[],ApiException::SUCCESS,'关注成功');

    }

    //一键关注多个标签
    public function batchTags(Request $request) {
        $validateRules = [
            'ids' => 'required|array',
        ];

        $this->validate($request,$validateRules);

        $ids = $request->input('ids');
        $user = $request->user();
        $fields = [];
        Attention::where("user_id",'=',$user->id)->where('source_type','=',Tag::class)->delete();
        foreach ($ids as $id) {
            $source = Tag::find($id);
            if(empty($source)){
                continue;
            }
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($source))->where('source_id','=',$id)->first();
            if($attention){
                continue;
            }

            $data = [
                'user_id'     => $user->id,
                'source_id'   => $id,
                'source_type' => get_class($source),
            ];

            $attention = Attention::create($data);

            $source->increment('followers');
            $fields[] = [
                'title' => '标签',
                'value' => $source->name
            ];
            $feed_event = 'tag_followed';
            $feed_target = $source->id.'_'.$user->id;
            if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                $this->credit($user->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source),false);
            }

        }
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']关注了标签',$fields));

        return self::createJsonData(true,[],ApiException::SUCCESS,'关注成功');
    }

    //一键关注多个问题
    public function batchQuestions(Request $request) {
        $validateRules = [
            'ids' => 'required|array',
        ];

        $this->validate($request,$validateRules);

        $ids = $request->input('ids');
        $user = $request->user();
        $fields = [];
        foreach ($ids as $id) {
            $source = Question::find($id);
            if(empty($source)){
                continue;
            }
            $fields[] = [
                'title' => '标题',
                'value' => $source->title
            ];
            $fields[] = [
                'title' => '地址',
                'value' => route('ask.question.detail',['id'=>$source->id])
            ];
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($source))->where('source_id','=',$id)->first();
            if($attention){
                continue;
            }

            $data = [
                'user_id'     => $user->id,
                'source_id'   => $id,
                'source_type' => get_class($source),
            ];

            $attention = Attention::create($data);

            $source->increment('followers');
            UserTag::multiIncrement($user->id,$source->tags()->get(),'questions');

            //产生一条feed流
            if ($source->question_type == 2) {
                $feed_event = 'question_followed';
                $feed_target = $source->id.'_'.$user->id;
                if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($feed_event,$feed_target,0)) {
                    feed()
                        ->causedBy($user)
                        ->performedOn($source)
                        ->log($user->name.'关注了问答', Feed::FEED_TYPE_FOLLOW_FREE_QUESTION);
                    $this->credit($user->id,Credit::KEY_NEW_FOLLOW,$attention->id,get_class($source),false);
                    if ($source->hide == 0) {
                        $this->credit($source->user_id,Credit::KEY_COMMUNITY_ASK_FOLLOWED,$attention->id,get_class($source),false);
                    }
                }
            }

        }
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']关注了问题',$fields));

        return self::createJsonData(true,[],ApiException::SUCCESS,'关注成功');
    }

    /*我的关注*/
    public function attentions(Request $request)
    {
        $uuid = $request->input('uuid',0);
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
        }
        $source_type = $request->route()->parameter('source_type');
        $sourceClassMap = [
            'questions' => 'App\Models\Question',
            'users' => 'App\Models\User',
            'tags' => 'App\Models\Tag',
            'products' => 'App\Models\Tag'
        ];

        if(!isset($sourceClassMap[$source_type])){
            abort(404);
        }

        $model = App::make($sourceClassMap[$source_type]);

        $query = $user->attentions()->where('source_type','=',$sourceClassMap[$source_type]);
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));

        $attentions = $query->orderBy('attentions.created_at','desc')->simplePaginate($perPage);
        $return = $attentions->toArray();
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
                    $attention = Attention::where("user_id",'=',$request->user()->id)->where('source_type','=',get_class($info))->where('source_id','=',$info->id)->first();
                    $item['is_following'] = 0;
                    if ($attention){
                        $item['is_following'] = 1;
                    }
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
                case 'tags':
                    $item['value'] = $info->id;
                    $item['text'] = $info->name;
                    $item['tag_logo'] = $info->logo;
                    $item['tag_summary'] = $info->summary;
                    break;
                case 'products':
                    $tagInfo = Tag::getReviewInfo($info->id);
                    $item['name'] = $info->name;
                    $item['logo'] = $info->logo;
                    $item['review_count'] = $tagInfo['review_count'];
                    $item['review_average_rate'] = $tagInfo['review_average_rate'];
                    break;
            }
            $data[] = $item;
        }
        $return['data'] = $data;
        return self::createJsonData(true,$return);
    }

    //关注标签的用户
    public function tagUsers(Request $request) {
        $validateRules = [
            'tag_name' => 'required'
        ];
        $this->validate($request,$validateRules);
        $tag = Tag::getTagByName($request->input('tag_name'));;
        $loginUser = $request->user();
        $attentions = Attention::where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($attentions as $attention) {
            $is_followed = 0;
            if($loginUser->id == $attention->user_id){
                $is_followed = 1;
            }else {
                $login_user_attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($loginUser))->where('source_id','=',$attention->user_id)->first();
                if ($login_user_attention){
                    $is_followed = 1;
                }
            }
            $item = [];
            $item['id'] = $attention->id;
            $item['user_id'] = $attention->user->id;
            $item['uuid'] = $attention->user->uuid;
            $item['user_name'] = $attention->user->name;
            $item['company'] = $attention->user->company;
            $item['title'] = $attention->user->title;
            $item['user_avatar_url'] = $attention->user->avatar;
            $item['is_expert'] = $attention->user->is_expert;
            $item['description'] = $attention->user->description;
            $item['is_followed'] = $is_followed;
            $data[] = $item;
        }
        $return = $attentions->toArray();
        $return['data'] = $data;
        return self::createJsonData(true,$return);
    }


    /*关注我的用户*/
    public function followMe(Request $request)
    {

        $uuid = $request->input('uuid',0);
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
        }
        $query = Attention::where('source_type','=','App\Models\User')->where('source_id',$user->id);

        $attentions = $query->orderBy('created_at','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $attentions->toArray();
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
            $attention = Attention::where("user_id",'=',$request->user()->id)->where('source_type','=',get_class($info))->where('source_id','=',$info->id)->first();
            $item['is_following'] = 0;
            if ($attention){
                $item['is_following'] = 1;
            }
            $item['user_avatar_url'] = $info->getAvatarUrl();
            $item['description'] = $info->description;
            $data[] = $item;
        }
        $return['data'] = $data;
        return self::createJsonData(true,$return);
    }


    //搜索我关注的用户
    public function searchFollowedUser(Request $request) {
        $name = $request->input('name');
        $directory = $request->input('directory',false);
        $query = $request->user()->attentions()->where('source_type','=','App\Models\User')
            ->leftJoin('users','attentions.source_id','=','users.id');
        if ($name) {
            $query = $query->where('users.name','like',$name.'%');
        }
        $users = $query->select('users.*','attentions.id as attention_id')->get();
        $data = [];
        foreach ($users as $user) {
            $authentication = Authentication::find($user->id);
            $item = [];
            $item['id'] = $user->attention_id;
            $item['user_id'] = $user->id;
            $item['uuid'] = $user->uuid;
            $item['user_name'] = $user->name;
            $item['spell'] = pinyin_permalink($user->name,'');
            $item['company'] = $user->company;
            $item['title'] = $user->title;
            $item['user_avatar_url'] = $user->avatar;
            $item['is_expert'] = ($authentication && $authentication->status === 1) ? 1 : 0;
            $item['description'] = $user->description;
            $item['is_followed'] = 1;
            $data[] = $item;
        }
        if ($directory) {
            $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
            $return = [];
            foreach ($letters as $letter) {
                foreach ($data as $val) {
                    if (stristr($val['spell'],$letter) == $val['spell']) {
                        $return[$letter][] = $val;
                    }
                }
            }
            return self::createJsonData(true,$return);
        }

        return self::createJsonData(true,$data);

    }

    //推荐一批用户
    public function recommendUserList(Request $request) {
        $user = $request->user();
        $tags = $user->userTags()->pluck('tag_id')->toArray();
        $attentionUsers = $user->attentions()->where('source_type','App\Models\User')->pluck('source_id')->toArray();
        $ignores = RateLimiter::instance()->sMembers('ignore_recommend_user_'.$user->id);
        if ($ignores) {
            $attentionUsers = array_merge($attentionUsers,$ignores);
        }
        $query = UserTag::select('user_id');
        $query1 = UserTag::select('user_id');

        $addressBookUids = [];
        if ($user->mobile) {
            //查找通讯录
            $addressBookUids = AddressBook::where('phone',$user->mobile)->get()->pluck('user_id')->toArray();
            $addressBookUids = array_unique($addressBookUids);
        }

        $attentionUsers[] = $user->id;
        $attentionUsers = array_unique(array_merge($attentionUsers,getSystemUids()));
        $banUsers = User::where('status',-1)->get()->pluck('id')->toArray();
        if ($banUsers) {
            $attentionUsers = array_unique(array_merge($attentionUsers,$banUsers));
        }

        if ($attentionUsers) {
            $query = $query->whereNotIn('user_id',$attentionUsers);
            $query1 = $query1->whereNotIn('user_id',$attentionUsers);
            $addressBookUids = array_diff($addressBookUids,$attentionUsers);
        }

        if ($tags) {
            $query = $query->whereIn('tag_id',$tags);
            if ($addressBookUids) {
                $query = $query->orWhereIn('user_id',$addressBookUids);
                $query1 = $query1->orWhereIn('user_id',$addressBookUids);
            }
            $query = $query->orderBy('skills','desc')->orderBy('answers','desc')->distinct();
            $query1 = $query1->orderBy(DB::raw('RAND()'))->distinct();
            $query = $query->union($query1);
            $userTags = $query->simplePaginate(10);
        } else {
            $query = $query->orderBy(DB::raw('RAND()'))->distinct();
            $userTags = $query->take(10)->get();
        }

        $data = [];
        foreach($userTags as $userTag){
            $info = User::find($userTag->user_id);
            if (!$info) continue;
            $tag = $info->userTag()->whereIn('tag_id',$tags)->orderBy('skills','desc')->first();
            $item = [];
            if ($tag) {
                if ($tag->skills > 0) {
                    $skillTags = Tag::select('name')->whereIn('id',$info->userSkillTag()->pluck('tag_id'))->distinct()->pluck('name')->toArray();
                    $item['description'] = '擅长';
                    foreach ($skillTags as $skillTag) {
                        $item['description'] .= '"'.$skillTag.'"';
                    }
                } elseif ($tag->answers > 0){
                    $answerTag = Tag::find($tag->tag_id);
                    $item['description'] = '曾在"'.$answerTag->name.'"下有回答';
                } else {
                    $item['description'] = '向您推荐';
                }
            } else {
                $item['description'] = '向您推荐';
            }
            $item['id'] = $info->id;
            $item['uuid'] = $info->uuid;
            $item['name'] = $info->name;
            $item['avatar_url'] = $info->avatar;
            $item['is_expert'] = $info->is_expert;
            $item['is_followed'] = 0;
            $data[] = $item;
        }
        return self::createJsonData(true,$data);
    }

    public function getRecommendUsers(Request $request) {
        $user = $request->user();
        $tags = $user->userTags()->pluck('tag_id')->toArray();
        $attentionUserIds = $user->attentions()->where('source_type','App\Models\User')->pluck('source_id')->toArray();
        $attentionUsers = $attentionUserIds;
        $ignores = RateLimiter::instance()->sMembers('ignore_recommend_user_'.$user->id);
        if ($ignores) {
            $attentionUsers = array_merge($attentionUsers,$ignores);
        }
        $addressBookUids = [];
        if ($user->mobile) {
            //查找通讯录
            $addressBookUids = AddressBook::where('phone',$user->mobile)->get()->pluck('user_id')->toArray();
            $addressBookUids = array_unique($addressBookUids);
        }

        $attentionUsers[] = $user->id;
        $attentionUsers = array_unique(array_merge($attentionUsers,getSystemUids()));
        $banUsers = User::where('status',-1)->get()->pluck('id')->toArray();
        if ($banUsers) {
            $attentionUsers = array_unique(array_merge($attentionUsers,$banUsers));
        }

        if ($attentionUsers) {
            $addressBookUids = array_diff($addressBookUids,$attentionUsers);
        }
        $data = [];
        $usedIds = [];
        //通讯录
        if ($addressBookUids) {
            foreach ($addressBookUids as $addressBookUid) {
                $info = User::find($addressBookUid);
                if (!$info) continue;
                $usedIds[] = $info->id;
                $item = [];
                $item['id'] = $info->id;
                $item['uuid'] = $info->uuid;
                $item['name'] = $info->name;
                $item['avatar_url'] = $info->avatar;
                $item['is_expert'] = $info->is_expert;
                $item['is_followed'] = 0;
                $item['description'] = '通讯录好友';
                $data[] = $item;
                if (count($data) >= 9) break;
            }
        }
        //关注他的人
        if (count($data) < 9) {
            $attentions = Attention::where('source_id',$user->id)->where('source_type',User::class)->pluck('user_id')->toArray();
            $attentions = array_diff($attentions,$attentionUsers);
            $attentions = array_diff($attentions,$addressBookUids);
            $count = 0;
            foreach ($attentions as $attention) {
                $info = User::find($attention);
                if (!$info) continue;
                $usedIds[] = $info->id;
                $count++;
                $item = [];
                $item['id'] = $info->id;
                $item['uuid'] = $info->uuid;
                $item['name'] = $info->name;
                $item['avatar_url'] = $info->avatar;
                $item['is_expert'] = $info->is_expert;
                $item['is_followed'] = 0;
                $item['description'] = 'Ta悄悄关注了你';
                $data[] = $item;
                if (count($data) >= 9 || $count >= 3) break;
            }
        }
        //共同好友
        if (count($data) < 9) {
            $attentionEachs = Attention::whereIn('source_id',array_diff($attentionUserIds,getSystemUids()))->where('source_type',User::class)->take(30)->get()->toArray();
            $used = array_column($data,'id');
            $count = 0;
            foreach ($attentionEachs as $attention) {
                if (in_array($attention['user_id'],$used) || in_array($attention['user_id'],$attentionUsers)) continue;
                $info = User::find($attention['user_id']);
                if (!$info) continue;
                $eachUser = User::find($attention['source_id']);
                if (!$eachUser) continue;
                if (in_array($info->id,$usedIds)) continue;
                $usedIds[] = $info->id;
                $count++;
                $item = [];
                $item['id'] = $info->id;
                $item['uuid'] = $info->uuid;
                $item['name'] = $info->name;
                $item['avatar_url'] = $info->avatar;
                $item['is_expert'] = $info->is_expert;
                $item['is_followed'] = 0;
                $item['description'] = '你们有共同好友：'.$eachUser->name;
                $data[] = $item;
                if (count($data) >= 9 || $count >= 3) break;
            }
        }
        //相同标签
        if (count($data) < 9) {
            $userTags = UserTag::whereIn('tag_id',$tags)->where('user_id','!=',$user->id)->orderBy('articles','desc')->take(30)->pluck('user_id')->toArray();
            $used = array_column($data,'id');
            $attentions = array_diff($userTags,$used);
            $count = 0;
            foreach ($attentions as $attention) {
                if (in_array($attention,$used) || in_array($attention,$attentionUsers)) continue;
                $info = User::find($attention);
                if (!$info) continue;
                if (in_array($info->id,$usedIds)) continue;
                $usedIds[] = $info->id;
                $count++;
                $item = [];
                $item['id'] = $info->id;
                $item['uuid'] = $info->uuid;
                $item['name'] = $info->name;
                $item['avatar_url'] = $info->avatar;
                $item['is_expert'] = $info->is_expert;
                $item['is_followed'] = 0;
                $item['description'] = '你可能会喜欢Ta的分享';
                $data[] = $item;
                if (count($data) >= 9 || $count >= 2) break;
            }
        }
        //领域优秀分享者
        if (count($data) < 9) {
            $userTags = UserTag::where('user_id','!=',$user->id)->orderBy('articles','desc')->take(20)->get();
            $used = array_column($data,'id');
            foreach ($userTags as $userTag) {
                if (in_array($userTag->user_id,$used) || in_array($userTag->user_id,$attentionUsers)) continue;
                $info = User::find($userTag->user_id);
                if (!$info) continue;
                if (in_array($info->id,$usedIds)) continue;
                $usedIds[] = $info->id;
                $item = [];
                $item['id'] = $info->id;
                $item['uuid'] = $info->uuid;
                $item['name'] = $info->name;
                $item['avatar_url'] = $info->avatar;
                $item['is_expert'] = $info->is_expert;
                $item['is_followed'] = 0;
                $item['description'] = $userTag->tag->name.'领域优秀分享者';
                $data[] = $item;
                if (count($data) >= 9) break;
            }
        }

        return self::createJsonData(true,$data);
    }

    public function ignoreRecommendUser(Request $request) {
        $validateRules = [
            'user_id' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user_id = $request->input('user_id');
        $loginUser = $request->user();
        RateLimiter::instance()->sAdd('ignore_recommend_user_'.$loginUser->id,$user_id,0);
        return self::createJsonData(true);
    }

}
