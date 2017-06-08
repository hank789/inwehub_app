<?php

namespace App\Http\Controllers\Admin;

use App\Models\Recommendation;
use App\Models\RecommendQa;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Config;

class RecommendQaController extends AdminController
{
    /*权限验证规则*/
    protected $validateRules = [
        'subject' => 'required|max:255',
        'user_name' => 'required|max:255',
        'user_avatar_url' => 'required|max:255',
        'price' => 'required|integer',
        'sort' => 'required|integer',
    ];



    /**
     * 显示推荐列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $recommendations = RecommendQa::orderBy('sort','asc')->orderBy('updated_at','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.operate.recommend_qa.index')->with('recommendations',$recommendations);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.operate.recommend_qa.create');
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
        RecommendQa::create($request->all());

        return $this->success(route('admin.operate.recommendQa.index'),'推荐添加成功');

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recommendation = RecommendQa::find($id);
        if(!$recommendation){
            return $this->error(route('admin.operate.recommendQa.index'),'推荐不存在，请核实');
        }
        return view('admin.operate.recommend_qa.edit')->with('recommendation',$recommendation);
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
        $recommendation = RecommendQa::find($id);
        if(!$recommendation){
            return $this->error(route('admin.recommendation.index'),'推荐不存在，请核实');
        }
        $this->validate($request,$this->validateRules);
        $recommendation->subject = $request->input('subject');
        $recommendation->user_name = $request->input('user_name');
        $recommendation->user_avatar_url = $request->input('user_avatar_url');
        $recommendation->price = $request->input('price');

        $recommendation->sort = $request->input('sort');
        $recommendation->status = $request->input('status');
        $recommendation->type   = $request->input('type');

        $recommendation->save();
        return $this->success(route('admin.operate.recommendQa.index'),'推荐修改成功');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        RecommendQa::destroy($request->input('ids'));
        return $this->success(route('admin.operate.recommendQa.index'),'推荐删除成功');
    }
}
