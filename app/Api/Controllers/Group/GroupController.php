<?php namespace App\Api\Controllers\Group;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SubmissionRecommend;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2018/4/8 下午2:35
 * @email: wanghui@yonglibao.com
 */

class GroupController extends Controller
{

    /*圈子创建校验*/
    protected $validateRules = [
        'name'    => 'required',
        'description' => 'required|max:500',
        'logo'=> 'required',
        'public'=> 'required|in:0,1'
    ];

    //创建圈子
    public function store(Request $request){
        $this->validate($request,$this->validateRules);
        $exist = Group::where('name',$request->input('name'))->first();
        if ($exist) {
            throw new ApiException(ApiException::GROUP_EXIST);
        }
        $user = $request->user();
        $base64 = $request->input('logo');
        $url = explode(';',$base64);
        $url_type = explode('/',$url[0]);
        $file_name = 'groups/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
        dispatch((new UploadFile($file_name,(substr($url[1],6)))));
        $img_url = Storage::disk('oss')->url($file_name);

        $group = Group::create([
            'user_id' => $user->id,
            'name'    => $request->input('name'),
            'description' => $request->input('description'),
            'public'  => $request->input('public',1),
            'logo'    => $img_url,
            'audit_status' => Group::AUDIT_STATUS_DRAFT,
            'subscribers'  => 1
        ]);
        GroupMember::create([
            'user_id'=>$user->id,
            'group_id'=>$group->id,
            'audit_status'=>Group::AUDIT_STATUS_SUCCESS
        ]);
        event(new SystemNotify('用户'.formatSlackUser($user).'创建了圈子:'.$group->name, $group->toArray()));
        return self::createJsonData(true,['id'=>$group->id]);
    }

    //修改圈子
    public function update(Request $request){
        $this->validateRules['id'] = 'required|integer';
        $this->validate($request,$this->validateRules);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        if ($group->name != $request->input('name')) {
            $exist = Group::where('name',$request->input('name'))->first();
            if ($exist) {
                throw new ApiException(ApiException::GROUP_EXIST);
            }
        }
        $img_url = $group->logo;
        $oldPublic = $group->public;
        $group->name = $request->input('name');
        $group->description = $request->input('description');
        $group->public = $request->input('public');

        $base64 = $request->input('logo');
        $url = explode(';',$base64);
        if(count($url) > 1){
            $url = explode(';',$base64);
            $url_type = explode('/',$url[0]);
            $file_name = 'groups/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
            dispatch((new UploadFile($file_name,(substr($url[1],6)))));
            $img_url = Storage::disk('oss')->url($file_name);
        }
        $group->logo = $img_url;
        $group->save();
        if ($oldPublic != $request->input('public')) Submission::where('group_id',$group->id)->update(['public'=>$group->public]);
        return self::createJsonData(true,['id'=>$group->id]);
    }

    //圈子详情
    public function detail(Request $request,JWTAuth $JWTAuth) {
        $this->validate($request,['id'=>'required|integer']);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
        }
        $return = $group->toArray();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        $return['is_joined'] = -1;
        if ($groupMember) {
            $return['is_joined'] = $groupMember->audit_status;
        }
        if ($user->id == $group->user_id) {
            $return['is_joined'] = 3;
        }
        //标记该用户已读圈子内文章
        RateLimiter::instance()->sAdd('group_read_users:'.$group->id,$user->id,0);
        $return['owner']['id'] = $group->user->id;
        $return['owner']['uuid'] = $group->user->uuid;
        $return['owner']['name'] = $group->user->name;
        $return['owner']['avatar'] = $group->user->avatar;
        $return['owner']['description'] = $group->user->description;
        $return['owner']['is_expert'] = $group->user->is_expert;
        $return['members'] = [];
        if ($group->public == 0 && in_array($return['is_joined'],[-1,0,2]) ) {
            //私有圈子
            return self::createJsonData(true,$return);
        }
        $room = Room::where('r_type',2)
            ->where('source_id',$group->id)
            ->where('source_type',get_class($group))
            ->where('status',Room::STATUS_OPEN)->first();
        $members = $group->members()->where('audit_status',1)->take(6)->orderBy(DB::raw('RAND()'))->get();
        foreach ($members as $member) {
            if ($member->user_id == $group->user_id) continue;
            $return['members'][] = [
                'id' => $member->user_id,
                'uuid' => $member->user->uuid,
                'name' => $member->user->name,
                'avatar' => $member->user->avatar,
                'description' => $member->user->description,
                'is_expert'   => $member->user->is_expert
            ];
        }
        $return['subscribers'] = $group->getHotIndex();
        $return['room_id'] = $room?$room->id:0;
        $return['unread_group_im_messages'] = 0;
        if ($room) {
            $return['unread_group_im_messages'] = RateLimiter::instance()->sIsMember('group_im_users:'.$room->id,$user->id)?0:1;
        }
        return self::createJsonData(true,$return);
    }

    //加入圈子
    public function join(Request $request){
        $this->validate($request,['id'=>'required|integer']);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        if ($group->audit_status != Group::AUDIT_STATUS_SUCCESS) {
            throw new ApiException(ApiException::GROUP_UNDER_AUDIT);
        }
        $user = $request->user();
        $user_ids = $request->input('user_ids');
        $audit_status = GroupMember::AUDIT_STATUS_DRAFT;
        if ($group->public) {
            $audit_status = GroupMember::AUDIT_STATUS_SUCCESS;
        }
        if ($user_ids) {
            foreach ($user_ids as $user_id) {
                GroupMember::firstOrCreate(['user_id'=>$user_id,'group_id'=>$group->id],['user_id'=>$user_id,'group_id'=>$group->id,'audit_status'=>$audit_status]);
            }
        } else if ($user->id != $group->user_id) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
            if (!$groupMember) {
                GroupMember::create([
                    'user_id'=>$user->id,
                    'group_id'=>$group->id,
                    'audit_status'=>$audit_status
                ]);
            } else if ($groupMember->audit_status == GroupMember::AUDIT_STATUS_REJECT) {
                $groupMember->audit_status = $audit_status;
                $groupMember->save();
            }
        }
        if ($group->public) {
            $group->subscribers = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->count();
            $group->save();
        }
        return self::createJsonData(true,[],ApiException::SUCCESS,$audit_status==GroupMember::AUDIT_STATUS_SUCCESS?'加入圈子成功':'您的入圈申请已提交');
    }

    //退出圈子
    public function quit(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        if ($groupMember) {
            $groupMember->delete();
            if ($group->subscribers > 0) $group->decrement('subscribers');
            event(new SystemNotify('用户'.formatSlackUser($user).'退出了圈子['.$group->name.']', []));
        }
        return self::createJsonData(true);
    }

    //审核通过加入圈子
    public function joinAgree(Request $request) {
        $this->validate($request,[
            'id'=>'required|integer',
            'user_id'=>'required|integer'
        ]);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) throw new ApiException(ApiException::BAD_REQUEST);
        $groupMember = GroupMember::where('user_id',$request->input('user_id'))->where('group_id',$group->id)->first();
        if ($groupMember) {
            $groupMember->audit_status = GroupMember::AUDIT_STATUS_SUCCESS;
            $groupMember->save();
            $group->increment('subscribers');
        }
        return self::createJsonData(true);
    }

    //审核不通过加入圈子
    public function joinReject(Request $request)
    {
        $this->validate($request,[
            'id'=>'required|integer',
            'user_id'=>'required|integer'
        ]);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) throw new ApiException(ApiException::BAD_REQUEST);
        $groupMember = GroupMember::where('user_id',$request->input('user_id'))->where('group_id',$group->id)->first();
        if ($groupMember) {
            $groupMember->audit_status = GroupMember::AUDIT_STATUS_REJECT;
            $groupMember->save();
        }
        return self::createJsonData(true);
    }

    //群主踢人功能
    public function removeMember(Request $request) {
        $this->validate($request,[
            'id'=>'required|integer',
            'user_id'=>'required|integer'
        ]);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) throw new ApiException(ApiException::BAD_REQUEST);
        $groupMember = GroupMember::where('user_id',$request->input('user_id'))->where('group_id',$group->id)->first();
        if ($groupMember) {
            $groupMember->delete();
        }
        return self::createJsonData(true);
    }

    //圈子内容设为推荐
    public function setSubmissionRecommend(Request $request) {
        $this->validate($request,[
            'submission_id'=>'required|integer'
        ]);
        $submission = Submission::find($request->input('submission_id'));
        $group = Group::find($submission->group_id);
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) throw new ApiException(ApiException::BAD_REQUEST);
        $submission->is_recommend = 1;
        $submission->save();
        if ($user->id != $submission->user_id) {
            $submission->user->notify(new SubmissionRecommend($submission->user_id,$submission));
        }
        event(new SystemNotify('圈主'.formatSlackUser($user).'设置圈子['.$group->name.']分享为推荐', [
            'text' => strip_tags($submission->title),
            'pretext' => '[链接]('.config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug.')',
            'author_name' => $user->name,
            'author_link' => config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug,
            'mrkdwn_in' => ['pretext'],
            'color'     => 'good',
            'fields' => []
        ]));
        return self::createJsonData(true);
    }

    //圈子分享列表
    public function submissionList(Request $request) {
        $this->validate($request,[
            'id'=>'required|integer',
            'type' => 'required|in:1,2,3'
        ]);
        $type = $request->input('type');
        $group = Group::find($request->input('id'));
        $user = $request->user();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();
        if (!$groupMember && $user->id != $group->user_id) {
            return self::createJsonData(false,['group_id'=>$group->id],ApiException::GROUP_NOT_JOINED,ApiException::$errorMessages[ApiException::GROUP_NOT_JOINED]);
        }

        $query = Submission::where('group_id',$request->input('id'));
        switch ($type) {
            case 1:
                //全部
                break;
            case 2:
                //圈主
                $query = $query->where('user_id',$group->user_id);
                break;
            case 3:
                //精华
                $query = $query->where('is_recommend',1);
                break;
        }

        $submissions = $query->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));

        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            //发布文章
            $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
            $url = $submission->data['url']??$comment_url;
            $support_uids = Support::where('supportable_id',$submission->id)
                ->where('supportable_type',Submission::class)->take(20)->pluck('user_id');
            $supporters = [];
            if ($support_uids) {
                $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
            }
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$submission->id)
                ->where('supportable_type',Submission::class)
                ->exists();
            $img = $submission->data['img']??'';
            $sourceData = [
                'title'     => $submission->partHtmlTitle(),
                'img'       => $img,
                'domain'    => $submission->data['domain']??'',
                'tags'      => $submission->tags()->get()->toArray(),
                'submission_id' => $submission->id,
                'current_address_name' => $submission->data['current_address_name']??'',
                'current_address_longitude' => $submission->data['current_address_longitude']??'',
                'current_address_latitude'  => $submission->data['current_address_latitude']??'',
                'comment_url' => $comment_url,
                'comment_number' => $submission->comments_number,
                'support_number' => $submission->upvotes,
                'supporter_list' => $supporters,
                'is_upvoted'     => $upvote ? 1 : 0,
                'submission_type' => $submission->type,
                'comments' => $submission->comments()->with('owner','children')->where('parent_id', 0)->orderBy('id','desc')->take(8)->get(),
                'group'    => null
            ];

            $list[] = [
                'id' => $submission->id,
                'title' => $submission->user->name.'发布了'.($submission->type == 'link' ? '文章':'分享'),
                'top' => 0,
                'user'  => [
                    'id'    => $submission->user->id ,
                    'uuid'  => $submission->user->uuid,
                    'name'  => $submission->user->name,
                    'is_expert' => $submission->user->is_expert,
                    'avatar'=> $submission->user->avatar
                ],
                'feed'  => $sourceData,
                'url'   => $url,
                'feed_type'  => Feed::FEED_TYPE_SUBMIT_READHUB_ARTICLE,
                'created_at' => (string)$submission->created_at
            ];

        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //圈子成员
    public function members(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $type = $request->input('type',2);
        $user = $request->user();
        $query = GroupMember::where('group_id',$request->input('id'));
        if ($type == 2) {
            $query = $query->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('id','asc');
        } elseif($type == 3) {
            //待审核的
            $query = $query->where('audit_status',GroupMember::AUDIT_STATUS_DRAFT)->orderBy('id','desc');
        } else {
            $query = $query->orderBy('id','desc');
        }
        $group = Group::find($request->input('id'));
        $page = $request->input('page',1);
        $members = $query->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $members->toArray();
        $return['data'] = [];
        if ($page == 1) {
            $ownerMember = GroupMember::where('group_id',$request->input('id'))->where('user_id',$group->user_id)->first();
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$ownerMember->user_id)->first();
            $return['data'][] = [
                'id' => $ownerMember->id,
                'user_id' => $ownerMember->user_id,
                'uuid' => $ownerMember->user->uuid,
                'user_name' => $ownerMember->user->name,
                'user_avatar_url' => $ownerMember->user->avatar,
                'audit_status' => $ownerMember->audit_status,
                'description' => $ownerMember->user->description,
                'is_expert'   => $ownerMember->user->is_expert,
                'is_followed' => $attention?1:0,
                "title" => $ownerMember->user->title,
                "company" =>  $ownerMember->user->company,//公司
                "created_at" => (string) $ownerMember->created_at
            ];
        }
        foreach ($members as $member) {
            if ($member->user_id == $group->user_id) continue;
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$member->user_id)->first();
            $return['data'][] = [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'uuid' => $member->user->uuid,
                'user_name' => $member->user->name,
                'user_avatar_url' => $member->user->avatar,
                'audit_status' => $member->audit_status,
                'description' => $member->user->description,
                'is_expert'   => $member->user->is_expert,
                'is_followed' => $attention?1:0,
                "title" => $member->user->title,
                "company" =>  $member->user->company,//公司
                "created_at" => (string) $member->created_at
            ];
        }
        return self::createJsonData(true,$return);
    }

    //我的圈子
    public function mine(Request $request) {
        $user = $request->user();
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $groupMembers = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('id','asc')->simplePaginate($perPage);
        $return = $groupMembers->toArray();
        $return['data'] = [];
        foreach ($groupMembers as $groupMember) {
            $group = $groupMember->group;
            $return['data'][] = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'logo' => $group->logo,
                'public' => $group->public,
                'subscribers' => $group->getHotIndex(),
                'articles'    => $group->articles,
                'is_joined'   => 1,
                'audit_status' => $group->audit_status,
                'unread_count' => RateLimiter::instance()->sIsMember('group_read_users:'.$group->id,$user->id)?0:1,
                'owner' => [
                    'id' => $group->user->id,
                    'uuid' => $group->user->uuid,
                    'name' => $group->user->name,
                    'avatar' => $group->user->avatar,
                    'description' => $group->user->description,
                    'is_expert' => $group->user->is_expert
                ]
            ];
        }
        return self::createJsonData(true,$return);
    }

    //推荐圈子
    public function recommend(Request $request) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->orderBy('subscribers','desc')->simplePaginate($perPage);
        $return = $groups->toArray();
        $return['data'] = [];
        $user = $request->user();
        foreach ($groups as $group) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
            $is_joined = -1;
            if ($groupMember) {
                $is_joined = $groupMember->audit_status;
            }
            if ($user->id == $group->user_id) {
                $is_joined = 3;
            }
            $return['data'][] = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'logo' => $group->logo,
                'public' => $group->public,
                'subscribers' => $group->getHotIndex(),
                'articles'    => $group->articles,
                'is_joined'  => $is_joined,
                'owner' => [
                    'id' => $group->user->id,
                    'uuid' => $group->user->uuid,
                    'name' => $group->user->name,
                    'avatar' => $group->user->avatar,
                    'description' => $group->user->description,
                    'is_expert' => $group->user->is_expert
                ]
            ];
        }
        return self::createJsonData(true,$return);
    }

    //随机推荐圈子内容
    public function hotRecommend(Request $request){
        $submissions = Submission::where('public',1)->where('upvotes','>',1)->orderBy(DB::raw('RAND()'))->take(5)->get();
        $return = $submissions->toArray();
        $list = [];
        $user = $request->user();
        foreach ($submissions as $submission) {
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$submission['id'])
                ->where('supportable_type',Submission::class)
                ->exists();
            $bookmark = Collection::where('user_id',$user->id)
                ->where('source_id',$submission['id'])
                ->where('source_type',Submission::class)
                ->exists();
            $group = Group::find($submission->group_id);
            $item = $submission->toArray();
            $item['title'] = strip_tags($item['title'],'<a><span>');
            $item['is_upvoted'] = $upvote ? 1 : 0;
            $item['is_bookmark'] = $bookmark ? 1: 0;
            $item['tags'] = $submission->tags()->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $item['group'] = $group->toArray();
            $item['group']['subscribers'] = $group->getHotIndex();
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //开启群聊
    public function openIm(Request $request) {
        $this->validate($request,[
            'id'=>'required|integer'
        ]);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) throw new ApiException(ApiException::BAD_REQUEST);
        $room = Room::where('r_type',2)
            ->where('source_id',$group->id)
            ->where('source_type',get_class($group))->first();
        if (!$room) {
            $room = Room::create([
                'user_id' => $user->id,
                'r_type'  => 2,
                'r_name'  => $group->name,
                'r_description' => $group->description,
                'source_id' => $group->id,
                'source_type' => get_class($group)
            ]);
            RoomUser::firstOrCreate([
                'user_id' => $user->id,
                'room_id' => $room->id
            ],[
                'user_id' => $user->id,
                'room_id' => $room->id
            ]);
        } else {
            $room->status = Room::STATUS_OPEN;
            $room->save();
        }
        return self::createJsonData(true,['room_id'=>$room->id]);
    }

    //关闭群聊
    public function closeIm(Request $request) {
        $this->validate($request,[
            'id'=>'required|integer'
        ]);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        if ($user->id != $group->user_id) throw new ApiException(ApiException::BAD_REQUEST);
        $room = Room::where('r_type',2)
            ->where('source_id',$group->id)
            ->where('source_type',get_class($group))->first();
        if ($room) {
            $room->status = Room::STATUS_CLOSED;
            $room->save();
        }
        return self::createJsonData(true);
    }

}