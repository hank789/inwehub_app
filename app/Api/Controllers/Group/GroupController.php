<?php namespace App\Api\Controllers\Group;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

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
        return self::createJsonData(true,['id'=>$group->id]);
    }

    //圈子详情
    public function detail(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        $return = $group->toArray();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        $return['is_joined'] = -1;
        if ($groupMember) {
            $return['is_joined'] = $groupMember->audit_status;
        }
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
        if ($group->public == 0 && in_array($return['is_joined'],[-1,0,2]) ) {
            //私有圈子
            return self::createJsonData(true,$return);
        }
        $members = $group->members()->where('audit_status',1)->take(20);
        foreach ($members as $member) {
            $return['members'][] = [
                'id' => $member->user_id,
                'uuid' => $member->user->uuid,
                'name' => $member->user->name,
                'avatar' => $member->user->avatar,
                'description' => $member->user->description,
                'is_expert'   => $member->user->is_expert
            ];
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
        return self::createJsonData(true);
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
            $group->decrement('subscribers');
        }
        return self::createJsonData(true);
    }

    //审核通过加入圈子
    public function joinAgree(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
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
        $this->validate($request,['id'=>'required|integer']);
        $group = Group::find($request->input('id'));
        if (!$group) {
            throw new ApiException(ApiException::GROUP_NOT_EXIST);
        }
        $user = $request->user();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        if ($groupMember) {
            $groupMember->audit_status = GroupMember::AUDIT_STATUS_REJECT;
            $groupMember->save();
        }
        return self::createJsonData(true);
    }

    //圈子分享列表
    public function submissionList(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $submissions = Submission::where('group_id',$request->input('id'))->orderBy('rate','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $user = $request->user();
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$submission['id'])
                ->where('supportable_type',Submission::class)
                ->exists();
            $bookmark = Collection::where('user_id',$user->id)
                ->where('source_id',$submission['id'])
                ->where('source_type',Submission::class)
                ->exists();
            $item = $submission->toArray();
            $item['title'] = strip_tags($item['title'],'<a><span>');
            $item['is_upvoted'] = $upvote ? 1 : 0;
            $item['is_bookmark'] = $bookmark ? 1: 0;
            $item['tags'] = $submission->tags()->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //圈子成员
    public function members(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $user = $request->user();
        $members = GroupMember::where('group_id',$request->input('id'))->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('id','asc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $members->toArray();
        $return['data'] = [];
        foreach ($members as $member) {
            $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$member->user_id)->first();
            $return['data'][] = [
                'id' => $member->user_id,
                'uuid' => $member->user->uuid,
                'name' => $member->user->name,
                'avatar' => $member->user->avatar,
                'description' => $member->user->description,
                'is_expert'   => $member->user->is_expert,
                'is_followed' => $attention?1:0
            ];
        }
        return self::createJsonData(true,$return);
    }

    //圈子精华
    public function recommendList(Request $request) {
        $this->validate($request,['id'=>'required|integer']);
        $submissions = Submission::where('group_id',$request->input('id'))->where('is_recommend',1)->orderBy('rate','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $user = $request->user();
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$submission['id'])
                ->where('supportable_type',Submission::class)
                ->exists();
            $bookmark = Collection::where('user_id',$user->id)
                ->where('source_id',$submission['id'])
                ->where('source_type',Submission::class)
                ->exists();
            $item = $submission->toArray();
            $item['title'] = strip_tags($item['title'],'<a><span>');
            $item['is_upvoted'] = $upvote ? 1 : 0;
            $item['is_bookmark'] = $bookmark ? 1: 0;
            $item['tags'] = $submission->tags()->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //我的圈子
    public function mine(Request $request) {
        $user = $request->user();
        $groups = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $groups->toArray();
        $return['data'] = [];
        foreach ($groups as $group) {
            $return['data'][] = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'logo' => $group->logo,
                'public' => $group->public,
                'subscribers' => $group->subscribers,
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

}