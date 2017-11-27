<?php

namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Company\CompanyData;
use App\Models\Company\CompanyDataUser;
use App\Models\Tag;
use Illuminate\Http\Request;

class DataController extends AdminController
{

    protected $validateRules = [
        'name' => 'required|max:255',
        'logo' => 'required|max:255',
        'address_province' => 'required|max:255',
        'address_detail' => 'required',
        'longitude' => 'required',
        'latitude' => 'required',
    ];

    /**
     * 显示列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = CompanyData::query();

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', '%'.$filter['word'].'%');
        }

        $companies = $query->orderBy('id','desc')->orderBy('updated_at','desc')->paginate(20);
        return view("admin.company.data.index")->with('companies',$companies)->with('filter',$filter);
    }

    public function create()
    {
        return view('admin.company.data.create');
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = CompanyData::find($id);
        if(!$company){
            return $this->error(route('admin.company.data.index'),'企业不存在，请核实');
        }

        return view('admin.company.data.edit')->with('company',$company);
    }

    public function store(Request $request)
    {
        $request->flash();
        $this->validate($request,$this->validateRules);
        $company = CompanyData::create($request->all());
        /*添加标签*/
        $tagString = $request->input('tags_id');
        if ($tagString) {
            Tag::multiSaveByIds($tagString,$company);
        }
        return $this->success(route('admin.company.data.index'),'企业添加成功');

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
        $company = CompanyData::find($id);
        if(!$company){
            return $this->error(route('admin.company.data.index'),'企业不存在，请核实');
        }

        $this->validate($request,$this->validateRules);

        $company->name = $request->input('name');
        $company->audit_status = $request->input('audit_status');
        $company->logo = $request->input('logo');
        $company->address_province = $request->input('address_province');
        $company->address_detail = $request->input('address_detail');
        $company->longitude = $request->input('longitude');
        $company->latitude = $request->input('latitude');
        $company->save();
        /*添加标签*/
        $tagString = $request->input('tags_id');
        if ($tagString) {
            Tag::multiSaveByIds($tagString,$company);
        }

        return $this->success(route('admin.company.data.index'),'企业修改成功');
    }

    public function verify(Request $request) {
        $ids = $request->input('ids');
        CompanyData::whereIn('id',$ids)->update(['audit_status'=>1]);

        return $this->success(route('admin.company.data.index'),'审核成功');
    }

    public function unverify(Request $request) {
        $ids = $request->input('ids');
        CompanyData::whereIn('id',$ids)->update(['audit_status'=>0]);

        return $this->success(route('admin.company.data.index'),'审核不通过成功');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        CompanyData::destroy($request->input('ids'));
        return $this->success(route('admin.company.data.index'),'企业删除成功');
    }


    public function people(Request $request){
        $filter =  $request->all();

        $query = CompanyDataUser::query();

        if( isset($filter['data_id']) && $filter['data_id'] ){
            $query->where('company_data_id',$filter['data_id']);
        }

        if( isset($filter['user_id']) && $filter['user_id'] ){
            $query->where('user_id',$filter['user_id']);
        }

        $companies = $query->orderBy('id','desc')->paginate(20);
        return view("admin.company.data.people")->with('companies',$companies)->with('filter',$filter);
    }

    public function createPeople(Request $request) {
        $data_id = $request->input('data_id');
        return view('admin.company.data.createPeople')->with('data_id',$data_id);
    }

    public function storePeople(Request $request) {
        $request->flash();
        $validateRules = [
            'company_data_id' => 'required|max:255',
            'user_id' => 'required|max:255',
            'status' => 'required|max:255',
            'audit_status' => 'required'
        ];
        $this->validate($request,$validateRules);
        CompanyDataUser::create($request->all());
        return $this->success(route('admin.company.data.people'),'企业人员添加成功');
    }

    public function editPeople($id) {
        $company = CompanyDataUser::find($id);
        if(!$company){
            return $this->error(route('admin.company.data.people'),'企业不存在，请核实');
        }

        return view('admin.company.data.editPeople')->with('company',$company);
    }

    public function updatePeople(Request $request){
        $request->flash();
        $id = $request->input('id');
        $company = CompanyDataUser::find($id);
        if(!$company){
            return $this->error(route('admin.company.data.people'),'企业不存在，请核实');
        }

        $validateRules = [
            'company_data_id' => 'required|max:255',
            'user_id' => 'required|max:255',
            'status' => 'required|max:255',
            'audit_status' => 'required'
        ];
        $this->validate($request,$validateRules);

        $company->company_data_id = $request->input('company_data_id');
        $company->user_id = $request->input('user_id');
        $company->status = $request->input('status');
        $company->audit_status = $request->input('audit_status');
        $company->save();

        return $this->success(route('admin.company.data.people'),'修改成功');
    }

    public function destroyPeople(Request $request){
        CompanyDataUser::destroy($request->input('ids'));
        return $this->success(route('admin.company.data.people'),'企业人员删除成功');
    }

    public function verifyPeople(Request $request) {
        $ids = $request->input('ids');
        CompanyDataUser::whereIn('id',$ids)->update(['audit_status'=>1]);

        return $this->success(route('admin.company.data.people'),'审核成功');
    }

    public function unverifyPeople(Request $request) {
        $ids = $request->input('ids');
        CompanyDataUser::whereIn('id',$ids)->update(['audit_status'=>0]);

        return $this->success(route('admin.company.data.people'),'审核不通过成功');
    }
}
