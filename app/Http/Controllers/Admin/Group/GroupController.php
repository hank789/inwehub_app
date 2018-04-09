<?php namespace App\Http\Controllers\Admin\Group;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Groups\Group;
use App\Models\UserOauth;
use App\Notifications\GroupAuditResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * @author: wanghui
 * @date: 2018/3/9 下午1:53
 * @email: wanghui@yonglibao.com
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
            'public' => 'required'
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
            Group::create([
                'name'         => $request->input('name'),
                'audit_status' => $request->input('audit_status'),
                'description'  => $request->input('description'),
                'public'       => $request->input('public'),
                'user_id'      => $request->input('author_id'),
                'logo'         => $img_url
            ]);
        } else {
            $group->audit_status = $request->input('audit_status');
            $group->name = $request->input('name');
            $group->description = $request->input('description');
            $group->public = $request->input('public');
            $group->user_id = $request->input('author_id');

            if ($img_url) {
                $group->logo = $img_url;
            }
            $group->save();
        }


        return $this->success(route('admin.group.index'),'圈子操作成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        Group::whereIn('id',$ids)->update(['status'=>Group::AUDIT_STATUS_SUCCESS]);
        foreach ($ids as $id) {
            $group = Group::find($id);
            $group->user->notify(new GroupAuditResult($group->user_id,$group));
        }
        return $this->success(route('admin.group.index'),'审核成功');

    }

    /**
     * 审核不通过
     */
    public function cancelVerify(Request $request)
    {
        $ids = $request->input('id');
        Group::whereIn('id',$ids)->update(['status'=>Group::AUDIT_STATUS_REJECT]);
        foreach ($ids as $id) {
            $group = Group::find($id);
            $group->user->notify(new GroupAuditResult($group->user_id,$group));
        }
        return $this->success(route('admin.group.index'),'圈子审核未通过');

    }

}