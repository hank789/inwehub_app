<?php

namespace App\Http\Controllers\Admin;

use App\Events\Frontend\System\Credit;
use App\Models\Area;
use App\Models\Authentication;
use App\Models\Readhub\ReadHubUser;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\AuthenticationUpdated;
use App\Services\City\CityData;
use Illuminate\Http\Request;

use App\Http\Requests;

class AuthenticationController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Authentication::query();
        $filter =  $request->all();

        $filter['category_id'] = $request->input('category_id',-1);

        /*认证申请状态过滤*/
        if(isset($filter['status']) && $filter['status'] > -1){
            $query->where('status','=',$filter['status']);
        }

        if( $filter['category_id'] > 0 ){
            $query->where('category_id','=',$filter['category_id']);
        }

        $authentications = $query->orderBy('updated_at','desc')->paginate(20);
        return view('admin.authentication.index')->with(compact('filter','authentications'));
    }

    public function create(){
        return view('admin.authentication.create');
    }

    public function store(Request $request){
        $data = $request->all();
        if($data['skill_tags'] !== null ){
            $skill_tags = $data['skill_tags'];
            $tags = Tag::whereIn('id',explode(',',$skill_tags))->get();
            UserTag::detachByField($data['user_id'],'skills');
            UserTag::multiIncrement($data['user_id'],$tags,'skills');
        }

        $object = Authentication::create($data);
        if($object && isset($data['status']) && $data['status'] == 1){
            $action = \App\Models\Credit::KEY_EXPERT_VALID;
            $object->user->notify(new AuthenticationUpdated($object));
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

        $validateRules = [
            'failed_reason' => 'required_if:status,4'
        ];

        $this->validate($request, $validateRules);

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
            $action = \App\Models\Credit::KEY_EXPERT_VALID;
            event(new Credit($authentication->user_id,$action,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$id,'专家认证'));
        }
        if ($old_status != $new_status && $new_status != 0) {
            $user = $authentication->user;
            $user->notify(new AuthenticationUpdated($authentication));
        }

        return $this->success(route('admin.authentication.index'),'行家认证信息修改成功');


    }

    /*修改分类*/
    public function changeCategories(Request $request){
        $ids = $request->input('ids','');
        $categoryId = $request->input('category_id',0);
        if($ids){
            Authentication::whereIn('user_id',explode(",",$ids))->update(['category_id'=>$categoryId]);
        }
        return $this->success(route('admin.authentication.index'),'分类修改成功');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('id');
        Authentication::destroy($ids);
        return $this->success(route('admin.authentication.index'),'行家认证信息删除成功');
    }
}
