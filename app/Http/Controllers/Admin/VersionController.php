<?php namespace App\Http\Controllers\Admin;
/**
 * @author: wanghui
 * @date: 2017/5/18 下午6:37
 * @email: wanghui@yonglibao.com
 */
use App\Models\AppVersion;
use Illuminate\Http\Request;

class VersionController extends AdminController
{

    /*新闻创建校验*/
    protected $validateRules = [
        'app_version'        => 'required|regex:/^([0-9]+.[0-9]+.[0-9])/',
        'package_url' => 'required|max:255',
        'is_ios_force' => 'required|in:0,1,2',
        'is_android_force' => 'required|in:0,1,2',
        'update_msg' => 'required|max:255',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = AppVersion::query();

        /*版本好过滤*/
        if( isset($filter['app_version']) && $filter['app_version'] ){
            $query->where('app_version','like', '%'.$filter['app_version'].'%');
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }

        $versions = $query->orderBy('app_version','desc')->paginate(20);
        return view("admin.appVersion.index")->with('versions',$versions)->with('filter',$filter);
    }



    public function create()
    {
        return view("admin.appVersion.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $loginUser = $request->user();

        $request->flash();

        $this->validateRules['app_version'] = 'required|regex:/^([0-9]+.[0-9]+.[0-9])/|unique:app_version';

        $this->validate($request,$this->validateRules);

        $data = [
            'user_id'      => $loginUser->id,
            'app_version'        => trim($request->input('app_version')),
            'package_url'  =>$request->input('package_url'),
            'is_ios_force' => $request->input('is_ios_force'),
            'is_android_force' => $request->input('is_android_force'),
            'update_msg'   => $request->input('update_msg'),
            'status'       => 0,
        ];

        $version = AppVersion::create($data);

        if($version){
            $message = '发布成功,等待管理员审核! ';
            return $this->success(route('admin.appVersion.index'),$message);
        }

        return  $this->error("发布失败，请稍后再试",route('admin.appVersion.index'));

    }

    /**
     * 显示文字编辑页面
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $version = AppVersion::find($id);

        if(!$version){
            abort(404);
        }

        return view("admin.appVersion.edit")->with(compact('version'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $version = AppVersion::find($id);
        if(!$version){
            abort(404);
        }

        $request->flash();

        $this->validate($request,$this->validateRules);

        $version->app_version = trim($request->input('app_version'));
        $version->package_url = trim($request->input('package_url'));
        $version->is_ios_force = trim($request->input('is_ios_force'));
        $version->is_android_force = trim($request->input('is_android_force'));
        $version->update_msg = $request->input('update_msg');

        $version->save();

        return $this->success(route('admin.appVersion.index'),"编辑成功");

    }

    /**
     * 删除
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        AppVersion::whereIn('id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.appVersion.index'),'禁用成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        AppVersion::whereIn('id',$ids)->update(['status'=>1]);

        return $this->success(route('admin.appVersion.index'),'审核成功');

    }

}