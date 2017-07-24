<?php

namespace App\Http\Controllers\Admin\Project;

use App\Events\Frontend\System\Credit;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Area;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Company\Project;
use App\Models\Company\ProjectDetail;
use App\Models\Tag;
use App\Models\UserTag;
use App\Services\City\CityData;
use Illuminate\Http\Request;

use App\Http\Requests;

class ProjectController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Project::query();
        $filter =  $request->all();

        /*认证申请状态过滤*/
        if(isset($filter['apply_status']) && $filter['apply_status'] > -1){
            $query->where('status','=',$filter['apply_status']);
        }

        if(isset($filter['user_id']) && $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        $projects = $query->orderBy('updated_at','desc')->paginate(20);
        return view('admin.project.index')->with(compact('filter','projects'));
    }

    public function destroy(Request $request)
    {
        Project::whereIn('id',$request->input('id'))->update(['status'=>Project::STATUS_REJECT]);
        return $this->success(route('admin.project.index'),'审核不通过成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        Project::whereIn('id',$ids)->update(['status'=>Project::STATUS_PUBLISH]);

        return $this->success(route('admin.project.index'),'审核成功');

    }

    public function view(Request $request)
    {
        $id = $request->input('id');
        $project = Project::find($id);
        $detail = ProjectDetail::find($id);

        return view('admin.project.detail')->with(compact('project','detail'));

    }

}
