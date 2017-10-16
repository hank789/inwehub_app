<?php namespace App\Http\Controllers\Admin;
/**
 * @author: wanghui
 * @date: 2017/5/18 下午6:37
 * @email: wanghui@yonglibao.com
 */
use App\Logic\TaskLogic;
use App\Models\AppVersion;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Task::query();

        if( isset($filter['uid']) && $filter['uid'] ){
            $query->where('user_id',$filter['uid']);
        }

        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        } elseif(!isset($filter['status'])) {
            $query->where('status','=',0);
            $filter['status'] = 0;
        }

        $tasks = $query->orderBy('id','desc')->paginate(20);
        $list = TaskLogic::formatList($tasks);

        return view("admin.task.index")->with('list',$list)->with('filter',$filter)->with('tasks',$tasks);
    }

    /**
     * 关闭任务
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function close(Request $request)
    {
        Task::whereIn('id',$request->input('id'))->update(['status'=>2]);
        return $this->success(route('admin.task.index'),'任务关闭成功');
    }

}