<?php

namespace App\Http\Controllers\Admin;

use App\Models\Readhub\Submission;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Config;

class RecommendReadController extends AdminController
{

    /**
     * 显示推荐列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $recommendations = Submission::where('recommend_status','>=',Submission::RECOMMEND_STATUS_PENDING)->orderBy('recommend_sort','desc')->orderBy('updated_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.operate.recommend_read.index')->with('recommendations',$recommendations);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recommendation = Submission::find($id);
        if(!$recommendation){
            return $this->error(route('admin.operate.recommendRead.index'),'推荐不存在，请核实');
        }
        return view('admin.operate.recommend_read.edit')->with('recommendation',$recommendation);
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
        $request->flash();
        $recommendation = Submission::find($id);
        if(!$recommendation){
            return $this->error(route('admin.operate.recommendRead.index'),'推荐不存在，请核实');
        }
        $validateRules = [
            'title'   => 'required',
            'img_url' => 'required',
            'recommend_status' => 'required|integer',
            'recommend_sort'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);

        $recommendation->title = $request->input('title');
        $recommendation->recommend_sort = $request->input('recommend_sort');
        $recommendation->recommend_status = $request->input('recommend_status');
        $object_data = $recommendation->data;
        $object_data['img'] = $request->input('img_url');
        $recommendation->data = $object_data;
        $recommendation->save();
        return $this->success(route('admin.operate.recommendRead.index'),'推荐修改成功');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Submission::whereIn('id',$request->input('ids'))->update(['recommend_status'=>Submission::RECOMMEND_STATUS_NOTHING]);
        return $this->success(route('admin.operate.recommendRead.index'),'推荐删除成功');
    }
}
