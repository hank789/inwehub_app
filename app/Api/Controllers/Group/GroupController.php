<?php namespace App\Api\Controllers\Group;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\OperationNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\RecommendGroupSubmission;
use App\Jobs\UploadFile;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Doing;
use App\Models\Feed\Feed;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SubmissionRecommend;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2018/4/8 下午2:35
 * @email: hank.huiwang@gmail.com
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

        $background_img_base64 = $request->input('background_img');
        if (!$background_img_base64) {
            $background_img = 'https://cdn.inwehub.com/system/group_18@3x.png';
        } else {
            $background_img_base64_arr = explode(';',$background_img_base64);
            $background_img_base64_type = explode('/',$background_img_base64_arr[0]);
            $file_name = 'groups/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$background_img_base64_type[1];
            dispatch((new UploadFile($file_name,(substr($background_img_base64_arr[1],6)))));
            $background_img = Storage::disk('oss')->url($file_name);
        }

        $group = Group::create([
            'user_id' => $user->id,
            'name'    => $request->input('name'),
            'description' => $request->input('description'),
            'public'  => $request->input('public',1),
            'logo'    => $img_url,
            'background_img' => $background_img,
            'audit_status' => Group::AUDIT_STATUS_DRAFT,
            'subscribers'  => 1
        ]);
        GroupMember::create([
            'user_id'=>$user->id,
            'group_id'=>$group->id,
            'audit_status'=>Group::AUDIT_STATUS_SUCCESS
        ]);
        $fields = [
            [
                'title' => '圈名',
                'value' => $group->name
            ],
            [
                'title' => '隐私',
                'value' => $group->public?'公开':'隐私'
            ],
            [
                'title' => '描述',
                'value' => $group->description
            ]
        ];
        event(new SystemNotify('@conan_wuhao 用户'.formatSlackUser($user).'创建了圈子:'.$group->name, $fields));
        self::$needRefresh = true;
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
            $group->logo = $img_url;
        }

        $background_img_base64 = $request->input('background_img');
        $background_img_base64_arr = explode(';',$background_img_base64);
        if (count($background_img_base64_arr) > 1) {
            $background_img_base64_type = explode('/',$background_img_base64_arr[0]);
            $file_name = 'groups/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$background_img_base64_type[1];
            dispatch((new UploadFile($file_name,(substr($background_img_base64_arr[1],6)))));
            $background_img = Storage::disk('oss')->url($file_name);
            $group->background_img = $background_img;
        }
        $group->save();
        if ($oldPublic != $request->input('public')) Submission::where('group_id',$group->id)->update(['public'=>$group->public]);
        self::$needRefresh = true;
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
            $user->name = '游客';
        }
        $return = $group->toArray();
        $return['subscribers'] = $group->getHotIndex();
        if ($group->audit_status == Group::AUDIT_STATUS_SYSTEM) {
            $return['subscribers'] += User::count();
        }
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        $return['is_joined'] = -1;
        $return['current_user_notify'] = 0;
        if ($groupMember) {
            $return['is_joined'] = $groupMember->audit_status;
            $return['current_user_notify'] = $groupMember->is_notify;
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
        $return['room_id'] = $room?$room->id:0;
        $return['recommend_submission_numbers'] = Submission::where('group_id',$group->id)->where('is_recommend',1)->count();
        $return['unread_group_im_messages'] = 0;
        if ($room) {
            $roomUser = RoomUser::where('user_id',$user->id)->where('room_id',$room->id)->first();
            if ($roomUser) {
                $return['unread_group_im_messages'] = MessageRoom::where('room_id',$room->id)->where('message_id','>',$roomUser->last_msg_id)->count();
            }
        }
        $this->doing($user,Doing::ACTION_VIEW_GROUP,get_class($group),$group->id,$group->name,'',0,0,'',
            config('app.mobile_url').'#/group/detail/'.$group->id);
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
        self::$needRefresh = true;
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
        self::$needRefresh = true;
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
        $fields = [
            [
                'title' => '标题',
                'value' => strip_tags($submission->title)
            ],
            [
                'title' => '链接',
                'value' => config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug
            ],
            [
                'title' => '作者',
                'value' => $user->name
            ]
        ];
        event(new OperationNotify('圈主'.formatSlackUser($user).'设置圈子['.$group->name.']分享为推荐', $fields));
        dispatch((new RecommendGroupSubmission($user->id,$submission->id))->delay(Carbon::now()->addMinutes(5)));
        return self::createJsonData(true);
    }

    public function cancelSubmissionRecommend(Request $request) {
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
        $submission->is_recommend = 0;
        $submission->save();
        if ($user->id != $submission->user_id) {
            //$submission->user->notify(new SubmissionRecommend($submission->user_id,$submission));
        }
        $fields = [
            [
                'title' => '标题',
                'value' => strip_tags($submission->title)
            ],
            [
                'title' => '链接',
                'value' => config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug
            ],
            [
                'title' => '作者',
                'value' => $user->name
            ]
        ];
        event(new SystemNotify('圈主'.formatSlackUser($user).'取消圈子['.$group->name.']分享为推荐', $fields));
        return self::createJsonData(true);
    }

    //置顶功能
    public function setSubmissionTop(Request $request) {
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
        $max = Submission::max('top');
        $submission->top = $max + 1;
        $submission->save();

        $fields = [
            [
                'title' => '标题',
                'value' => strip_tags($submission->title)
            ],
            [
                'title' => '链接',
                'value' => config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug
            ],
            [
                'title' => '作者',
                'value' => $user->name
            ]
        ];
        event(new OperationNotify('圈主'.formatSlackUser($user).'设置圈子['.$group->name.']分享为置顶', $fields));
        return self::createJsonData(true);
    }

    public function cancelSubmissionTop(Request $request) {
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
        $submission->top = 0;
        $submission->save();

        $fields = [
            [
                'title' => '标题',
                'value' => strip_tags($submission->title)
            ],
            [
                'title' => '链接',
                'value' => config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug
            ],
            [
                'title' => '作者',
                'value' => $user->name
            ]
        ];
        event(new SystemNotify('圈主'.formatSlackUser($user).'取消圈子['.$group->name.']分享为置顶', $fields));
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
        $limit = Config::get('inwehub.api_data_page_size');
        $page = $request->input('page',1);
        $alertMsg = '';
        $last_seen = RateLimiter::instance()->hGet('user_group_last_seen',$group->id.'_'.$user->id);
        $joined = true;
        if ($group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();
            if (!$groupMember && $user->id != $group->user_id) {
                if ($page >= 2) {
                    return self::createJsonData(false,['group_id'=>$group->id],ApiException::GROUP_NOT_JOINED,ApiException::$errorMessages[ApiException::GROUP_NOT_JOINED]);
                }
                //未加入圈子也显示10条
                $limit = 10;
                $page = 1;
                $joined = false;
                //return self::createJsonData(false,['group_id'=>$group->id],ApiException::GROUP_NOT_JOINED,ApiException::$errorMessages[ApiException::GROUP_NOT_JOINED]);
            }
        }

        $query = Submission::where('group_id',$request->input('id'))->where('status',1);
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

        $query = $query->orderBy('top','desc')->orderBy('id','desc');
        $submissions = $query->simplePaginate($limit,['*'],'page',$page);

        if ($page == 1 && $joined) {
            if ($last_seen) {
                $ids = $query->where('top',0)->take(100)->pluck('id')->toArray();
                $newCount = array_search($last_seen,$ids);
                if ($newCount === false) {
                    $newCount = '99+';
                }
                if ($newCount) {
                    $alertMsg = '更新了'.$newCount.'条信息';
                } else {
                    $alertMsg = '暂无新信息';
                }
            } else {
                $alertMsg = '已为您更新';
            }
        }

        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            if ($page == 1 && $last_seen < $submission->id) {
                $last_seen = $submission->id;
            }
            $list[] = $submission->formatListItem($user);
        }
        if ($page == 1 && $joined) {
            RateLimiter::instance()->hSet('user_group_last_seen',$group->id.'_'.$user->id,$last_seen);
        }
        $return['data'] = $list;
        $return['alert_msg'] = $alertMsg;
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
            $owner = $group->user;
            $ownerMember = GroupMember::where('group_id',$group->id)->where('user_id',$owner->id)->first();
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$ownerMember->user_id)->first();
            $return['data'][] = [
                'id' => $ownerMember->id,
                'user_id' => $owner->id,
                'uuid' => $owner->uuid,
                'user_name' => $owner->name,
                'user_avatar_url' => $owner->avatar,
                'audit_status' => GroupMember::AUDIT_STATUS_SUCCESS,
                'description' => $owner->description,
                'is_expert'   => $owner->is_expert,
                'is_followed' => $attention?1:0,
                "title" => $owner->title,
                "company" =>  $owner->company,//公司
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
        $uuid = $request->input('uuid',0);
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
            $this->doing($user,Doing::ACTION_VIEW_GROUP_LIST,'',0,'核心页面');
        }
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $groupMembers = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('updated_at','desc')->simplePaginate($perPage);
        $return = $groupMembers->toArray();
        $return['data'] = [];
        $otherGroups = [];
        foreach ($groupMembers as $groupMember) {
            $group = $groupMember->group;
            if ($group->audit_status == Group::AUDIT_STATUS_REJECT && $group->user_id != $user->id) continue;
            $item = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'logo' => $group->logo,
                'public' => $group->public,
                'subscribers' => $group->getHotIndex(),
                'articles'    => $group->articles,
                'is_joined'   => $groupMember->group->user_id == $user->id ? 3:1,
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
            if ($group->user_id == $user->id) {
                $return['data'][] = $item;
            } else {
                $otherGroups[] = $item;
            }
        }
        $return['data'] = array_merge($return['data'],$otherGroups);
        return self::createJsonData(true,$return);
    }

    public function getGroups(Request $request) {
        $uid = $request->input('token');
        if (!$uid) {
            return self::createJsonData(false);
        }
        $user = User::where('uuid',$uid)->first();
        if (!$user) {
            return self::createJsonData(false);
        }
        if (!$user->isRole('operatormanager')) {
            return self::createJsonData(false);
        }
        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->get();
        $return = [];
        foreach ($groups as $group) {
            $return[] = [
                'id' => $group->id,
                'name'=>$group->name
            ];
        }
        return self::createJsonData(true,$return);
    }


    public function getHotGroup(Request $request, JWTAuth $JWTAuth) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $days = $request->input('days',1);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $groups = [];
        if ($days == 1) {
            $groups = RateLimiter::instance()->zRevrange('group-daily-hot-'.date('Ymd'),0,$perPage-1);
        } else {
            for ($i=0;$i<$days;$i++) {
                $groupIds = RateLimiter::instance()->zRevrange('group-daily-hot-'.date('Ymd',strtotime('-'.$i.' days')),0,-1);
                foreach ($groupIds as $groupId => $hotScore) {
                    if (!isset($groups[$groupId])) $groups[$groupId] = 0;
                    $groups[$groupId] += $hotScore;
                }
            }
            arsort($groups);
        }
        $hotGroups = [];
        foreach ($groups as $groupId => $hotScore) {
            $group = Group::find($groupId);
            $hotGroups[] = [
                'id' => $groupId,
                'user_id' => $group->user_id,
                'name'    => $group->name,
                'description' => $group->description,
                'logo'    => $group->logo,
                'public'  => $group->public,
                'scores'  => $hotScore,
                'owner'   => [
                    'id' => $group->user_id,
                    'uuid' => $group->user->uuid,
                    'name' => $group->user->name,
                    'is_expert' => $group->user->is_expert,
                    'avatar' => $group->user->avatar
                ]
            ];
            if (count($hotGroups) >= $perPage) break;
        }
        return self::createJsonData(true,$hotGroups);
    }

    //推荐圈子
    public function recommend(Request $request) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $type = $request->input('type',1);
        $query = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->orderBy('subscribers','desc');
        $include_joined = true;
        switch ($type) {
            case 1:
                //全部圈子
                break;
            case 2:
                //公开圈子
                $query->where('public',1);
                $include_joined = false;
                break;
            case 3:
                //私密圈子
                $query->where('public',0);
                break;
        }
        $groups = $query->simplePaginate($perPage);
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
            if (($is_joined == 1 || $is_joined == 3) && !$include_joined) {
                continue;
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
        $submissions = Submission::where('public',1)->where('status',1)->where('upvotes','>',1)->orderBy(DB::raw('RAND()'))->take(5)->get();
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
            $item['tags'] = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
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
        if ($group->audit_status != Group::AUDIT_STATUS_SUCCESS) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
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

    //设置圈子通知
    public function setNotify(Request $request) {
        $this->validate($request,[
            'id'=>'required|integer',
            'is_notify' => 'required|integer|in:0,1'
        ]);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        if (!$groupMember) {
            throw new ApiException(ApiException::GROUP_NOT_JOINED);
        }
        $groupMember->is_notify = $request->input('is_notify');
        $groupMember->save();
        self::$needRefresh = true;
        return self::createJsonData(true,['is_notify'=>$request->input('is_notify')]);
    }

    //获取反馈圈子
    public function getHelpGroup(Request $request) {
        $group = Group::where('name','帮助与反馈')->first();
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        $return = $group->toArray();
        $return['is_joined'] = 1;
        if ($user->id == $group->user_id) {
            $return['is_joined'] = 3;
        }

        $return['owner']['id'] = $group->user->id;
        $return['owner']['uuid'] = $group->user->uuid;
        $return['owner']['name'] = $group->user->name;
        $return['owner']['avatar'] = $group->user->avatar;
        $return['owner']['description'] = $group->user->description;
        $return['owner']['is_expert'] = $group->user->is_expert;
        $return['members'] = [];
        $room = Room::where('r_type',2)
            ->where('source_id',$group->id)
            ->where('source_type',get_class($group))
            ->where('status',Room::STATUS_OPEN)->first();
        $return['subscribers'] = $group->getHotIndex();
        $return['room_id'] = $room?$room->id:0;
        $return['unread_group_im_messages'] = 0;
        if ($room) {
            $return['unread_group_im_messages'] = RateLimiter::instance()->sIsMember('group_im_users:'.$room->id,$user->id)?0:1;
        }
        return self::createJsonData(true,$return);
    }
}