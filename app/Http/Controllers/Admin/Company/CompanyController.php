<?php

namespace App\Http\Controllers\Admin\Company;

use App\Events\Frontend\System\Credit;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Area;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Tag;
use App\Models\UserTag;
use App\Services\City\CityData;
use Illuminate\Http\Request;

use App\Http\Requests;

class CompanyController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Company::query();
        $filter =  $request->all();

        /*认证申请状态过滤*/
        if(isset($filter['status']) && $filter['status'] > -1){
            $query->where('status','=',$filter['status']);
        }

        if( $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        $companies = $query->orderBy('updated_at','desc')->paginate(20);
        return view('admin.authentication.index')->with(compact('filter','companies'));
    }

    public function create(){
        return view('admin.authentication.create');
    }

    public function store(Request $request){
        $data = $request->all();

        \Log::info('test',$data);
        if($data['skill_tags'] !== null ){
            $skill_tags = $data['skill_tags'];
            $tags = Tag::whereIn('id',explode(',',$skill_tags))->get();
            UserTag::detachByField($data['user_id'],'skills');
            UserTag::multiIncrement($data['user_id'],$tags,'skills');
        }

        $object = Authentication::create($data);
        if($object && isset($data['status']) && $data['status'] == 1){
            $action = 'expert_valid';
            event(new Credit($data['user_id'],$action,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$data['user_id'],'专家认证'));
        }
        return $this->success(route('admin.authentication.index'),'行家认证信息添加成功');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $authentication = Authentication::find($id);
        return view('admin.authentication.edit')->with(compact('authentication'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $authentication = Authentication::find($id);
        if(!$authentication){
            return $this->error(route('admin.authentication.index'),'行家认证信息不存在，请核实');
        }


        $data = $request->all();

        $old_status = $authentication->status;
        $new_status = $data['status'];
        $authentication->update($data);

        if($data['skill_tags'] !== null ){
            $skill_tags = $data['skill_tags'];
            $tags = Tag::whereIn('id',explode(',',$skill_tags))->get();
            UserTag::detachByField($authentication->user_id,'skills');
            UserTag::multiIncrement($authentication->user_id,$tags,'skills');
        }

        if($old_status != 1 && $new_status == 1){
            $action = 'expert_valid';
            event(new Credit($authentication->user_id,$action,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$id,'专家认证'));
        }

        return $this->success(route('admin.authentication.index'),'行家认证信息修改成功');


    }

}
