<?php

namespace App\Http\Controllers\Admin;

use App\Models\Recommendation;
use App\Models\RecommendQa;
use App\Models\UserRegistrationCode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Config;

class RegistrationCodeController extends AdminController
{
    /*权限验证规则*/
    protected $validateRules = [
        'keyword' => 'required',
        'code' => 'required|max:6|unique:user_registration_code',
        'status' => 'required|in:0,1,2',
    ];



    /**
     * 显示列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();
        $query = UserRegistrationCode::orderBy('id','desc');
        if( isset($filter['keyword']) &&  $filter['keyword']){
            $query->where('keyword','=',$filter['keyword']);
        }
        if( isset($filter['code']) && $filter['code'] ){
            $query->where('code',$filter['code']);
        }

        if( isset($filter['status']) && $filter['status']>=0 ){
            $query->where('status',$filter['status']);
        }
        $codes = $query->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.operate.rgcode.index')->with('codes',$codes)->with('filter',$filter);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $code = UserRegistrationCode::genCode();
        return view('admin.operate.rgcode.create')->with('code',$code);
    }



    /**
     * 保存添加的推荐信息
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->flash();

        $this->validate($request,$this->validateRules);
        $data = $request->all();
        $data['recommend_uid'] = $request->user()->id;
        $data['expired_at']    = date('Y-m-d 23:59:59',strtotime('+3 days'));
        UserRegistrationCode::create($data);

        return $this->success(route('admin.operate.rgcode.index'),'添加成功');

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $code = UserRegistrationCode::find($id);
        if(!$code){
            return $this->error(route('admin.operate.rgcode.index'),'邀请码不存在，请核实');
        }
        return view('admin.operate.rgcode.edit')->with('code',$code);
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
        $code = UserRegistrationCode::find($id);
        if(!$code){
            return $this->error(route('admin.rgcode.index'),'邀请码不存在，请核实');
        }
        $this->validateRules['code'] = 'required|max:6|unique:user_registration_code,code,'.$code->id;

        $this->validate($request,$this->validateRules);
        $code->keyword = $request->input('keyword');
        $code->code = $request->input('code');
        $code->status = $request->input('status');

        $code->save();
        return $this->success(route('admin.operate.rgcode.index'),'修改成功');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        UserRegistrationCode::destroy($request->input('id'));
        return $this->success(route('admin.operate.rgcode.index'),'删除成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        UserRegistrationCode::whereIn('id',$ids)->update(['status'=>1]);

        return $this->success(route('admin.operate.rgcode.index'),'审核成功');

    }
}
