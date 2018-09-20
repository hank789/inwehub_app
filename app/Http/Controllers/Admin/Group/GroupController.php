<?php namespace App\Http\Controllers\Admin\Group;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\UserOauth;
use App\Notifications\GroupAuditResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2018/3/9 下午1:53
 * @email: hank.huiwang@gmail.com
 */


class GroupController extends AdminController {


    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Group::orderBy('created_at','desc');

        if(isset($filter['user_id']) && $filter['user_id'] > 0){
            $query->where("user_id","=",$filter['user_id']);
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -2 ){
            $query->where('audit_status','=',$filter['status']);
        }

        if (isset($filter['name']) && $filter['name']) {
            $query->where('name','like','%'.$filter['name'].'%');
        }

        $groups = $query->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.group.index')->with('groups',$groups)->with('filter',$filter);
    }

    public function create()
    {
        $group = Group::findOrNew(0);
        $group->id = 0;
        return view('admin.group.edit')->with('group',$group);
    }

    public function edit($id){
        $group = Group::find($id);
        return view('admin.group.edit')->with(compact('group'));
    }

    public function update(Request $request){
        $validateRules = [
            'id'      => 'required',
            'name'   => 'required',
            'audit_status' => 'required|integer',
            'description'   => 'required',
            'author_id' => 'required',
            'public' => 'required',
            'failed_reason' => 'required_if:audit_status,2'
        ];
        $this->validate($request,$validateRules);
        $group = Group::find($request->input('id'));
        $img_url = '';
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'groups/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        }
        if(!$group){
            $group = Group::create([
                'name'         => $request->input('name'),
                'audit_status' => $request->input('audit_status'),
                'description'  => $request->input('description'),
                'public'       => $request->input('audit_status') == Group::AUDIT_STATUS_SYSTEM?0:$request->input('public'),
                'user_id'      => $request->input('author_id'),
                'logo'         => $img_url
            ]);
            if ($request->input('audit_status') != Group::AUDIT_STATUS_SYSTEM) {
                GroupMember::create([
                    'user_id'=>$request->input('author_id'),
                    'group_id'=>$group->id,
                    'audit_status'=>Group::AUDIT_STATUS_SUCCESS
                ]);
            }
        } else {
            $oldStatus = $group->audit_status;
            $oldUserId = $group->user_id;
            $group->audit_status = $request->input('audit_status');
            $group->name = $request->input('name');
            $group->description = $request->input('description');
            $group->public = $request->input('audit_status') == Group::AUDIT_STATUS_SYSTEM?0:$request->input('public');
            $group->user_id = $request->input('author_id');
            $group->failed_reason = $request->input('failed_reason');

            if ($img_url) {
                $group->logo = $img_url;
            }
            $group->save();
            if ($oldStatus != $request->input('audit_status')) {
                $group->user->notify(new GroupAuditResult($group->user_id,$group));
            }
            if ($oldUserId != $request->input('author_id')) {
                if ($request->input('audit_status') != Group::AUDIT_STATUS_SYSTEM) {
                    GroupMember::firstOrCreate([
                        'user_id'=>$request->input('author_id'),
                        'group_id'=>$group->id
                    ],[
                        'user_id'=>$request->input('author_id'),
                        'group_id'=>$group->id,
                        'audit_status'=>Group::AUDIT_STATUS_SUCCESS
                    ]);
                }
            }
        }
        return $this->success(route('admin.group.index'),'圈子操作成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        Group::whereIn('id',$ids)->update(['audit_status'=>Group::AUDIT_STATUS_SUCCESS]);
        foreach ($ids as $id) {
            $group = Group::find($id);
            $group->user->notify(new GroupAuditResult($group->user_id,$group));
        }
        return $this->success(route('admin.group.index'),'审核成功');

    }

    public function destroy(Request $request)
    {
        $ids = $request->input('id');
        foreach ($ids as $id) {
            if (Submission::where('group_id',$id)->count() <= 0) {
                Group::destroy($id);
            }
        }
        return $this->success(url()->previous(),'删除成功，有文章的圈子不会被删除');
    }

}