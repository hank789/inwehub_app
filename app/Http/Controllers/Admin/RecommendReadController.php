<?php

namespace App\Http\Controllers\Admin;

use App\Models\Readhub\Submission;
use App\Models\RecommendRead;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class RecommendReadController extends AdminController
{

    /**
     * 显示推荐列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = RecommendRead::query();

        /*提问人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $word = str_replace('"','',json_encode($filter['word']));
            $word = str_replace("\\",'_',$word);
            $query->where('data','like', '%'.$word.'%');
        }

        $recommendations = $query->orderBy('sort','desc')->orderBy('updated_at','desc')->paginate(20);
        return view("admin.operate.recommend_read.index")->with('recommendations',$recommendations)->with('filter',$filter);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $recommendation = RecommendRead::find($id);
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
        $recommendation = RecommendRead::find($id);
        if(!$recommendation){
            return $this->error(route('admin.operate.recommendRead.index'),'推荐不存在，请核实');
        }
        $validateRules = [
            'title'   => 'required',
            'recommend_status' => 'required|integer',
            'recommend_sort'   => 'required|integer',
        ];
        $this->validate($request,$validateRules);
        $img_url = '';
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        } elseif (empty($recommendation->data['img'])) {
            return $this->error(route('admin.operate.recommendRead.edit',['id'=>$id]),'请上传封面图片');
        }

        $recommendation->sort = $request->input('recommend_sort');
        $recommendation->audit_status = $request->input('recommend_status');
        $object_data = $recommendation->data;
        $object_data['img'] = $img_url;
        $object_data['title'] = $request->input('title');
        $recommendation->data = $object_data;
        $recommendation->save();

        return $this->success(route('admin.operate.recommendRead.index'),'推荐修改成功');
    }

    public function verify(Request $request) {
        $ids = $request->input('ids');
        RecommendRead::whereIn('id',$ids)->update(['audit_status'=>1]);

        return $this->success(route('admin.operate.recommendRead.index'),'审核成功');
    }

    public function cancelVerify(Request $request) {
        $ids = $request->input('ids');
        RecommendRead::whereIn('id',$ids)->update(['audit_status'=>0]);

        return $this->success(route('admin.operate.recommendRead.index'),'取消推荐成功');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        RecommendRead::destroy($request->input('ids'));
        return $this->success(route('admin.operate.recommendRead.index'),'推荐删除成功');
    }
}
