<?php

namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Company\CompanyService;
use Illuminate\Http\Request;

class ServiceController extends AdminController
{

    protected $validateRules = [
        'title' => 'required|max:255',
        'img_url_slide' => 'required|max:255',
        'img_url_list' => 'required|max:255',
        'sort' => 'required'
    ];

    /**
     * 显示列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = CompanyService::query();

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }

        $services = $query->orderBy('sort','desc')->orderBy('updated_at','desc')->paginate(20);
        return view("admin.company.service.index")->with('services',$services)->with('filter',$filter);
    }

    public function create()
    {
        $service = CompanyService::orderBy('sort','DESC')->first();
        $sort = 1;
        if($service){
            $sort = $service->sort + 1;
        }
        return view('admin.company.service.create')->with('sort',$sort);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $service = CompanyService::find($id);
        if(!$service){
            return $this->error(route('admin.company.service.index'),'推荐不存在，请核实');
        }

        return view('admin.company.service.edit')->with('service',$service);
    }

    public function store(Request $request)
    {
        $request->flash();
        $this->validate($request,$this->validateRules);
        $img_url_slide = formatCdnUrl($request->input('img_url_slide'));
        $img_url_list = formatCdnUrl($request->input('img_url_list'));
        if (!$img_url_list || !$img_url_slide) {
            return $this->error(route('admin.company.service.create'),'url地址必须为cdn地址');
        }
        $data = $request->all();
        $data['img_url_slide'] = $img_url_slide;
        $data['img_url_list'] = $img_url_list;

        CompanyService::create($data);
        return $this->success(route('admin.company.service.index'),'服务添加成功');

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
        $request->flash();
        $id = $request->input('id');
        $service = CompanyService::find($id);
        if(!$service){
            return $this->error(route('admin.company.service.index'),'服务不存在，请核实');
        }

        $this->validate($request,$this->validateRules);

        $img_url_slide = formatCdnUrl($request->input('img_url_slide'));
        $img_url_list = formatCdnUrl($request->input('img_url_list'));
        if (!$img_url_list || !$img_url_slide) {
            return $this->error(route('admin.company.service.create'),'url地址必须为cdn地址');
        }


        $service->sort = $request->input('sort');
        $service->audit_status = $request->input('audit_status');
        $service->img_url_slide = $img_url_slide;
        $service->img_url_list = $img_url_list;
        $service->title = $request->input('title');
        $service->save();

        return $this->success(route('admin.company.service.index'),'服务修改成功');
    }

    public function verify(Request $request) {
        $ids = $request->input('ids');
        CompanyService::whereIn('id',$ids)->update(['audit_status'=>1]);

        return $this->success(route('admin.company.service.index'),'审核成功');
    }

    public function unverify(Request $request) {
        $ids = $request->input('ids');
        CompanyService::whereIn('id',$ids)->update(['audit_status'=>0]);

        return $this->success(route('admin.company.service.index'),'取消推荐成功');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        CompanyService::destroy($request->input('ids'));
        return $this->success(route('admin.company.service.index'),'推荐删除成功');
    }
}
