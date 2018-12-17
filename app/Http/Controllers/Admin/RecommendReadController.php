<?php

namespace App\Http\Controllers\Admin;

use App\Logic\TagsLogic;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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

        if( isset($filter['tags']) && $filter['tags'] ){
            $tagIds = explode(',',$filter['tags']);
            $tags = Tag::whereIn('id',$tagIds)->get();
            $filter['tags'] = $tags;
            $query->whereHas('tags', function($query) use ($tagIds) {
                $query->whereIn('tag_id', $tagIds);
            });
        } elseif (isset($filter['withoutTags']) && $filter['withoutTags']) {
            $query->doesntHave('tags');
        }
        if (isset($filter['sortByRate']) && $filter['sortByRate']) {
            $query->orderBy('rate','desc');
        }
        $data = TagsLogic::loadTags(6,'','id');
        $tags = $data['tags'];
        $recommendations = $query->orderBy('created_at','desc')->paginate(20);
        return view("admin.operate.recommend_read.index")->with('recommendations',$recommendations)->with('filter',$filter)->with('tags',$tags);
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
            'weight_rate' => 'required|numeric'
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
        $oldRate = $recommendation->getRateWeight();
        $oldStatus = $recommendation->audit_status;

        $recommendation->sort = $request->input('recommend_sort');
        $recommendation->tips = $request->input('tips');
        $recommendation->audit_status = $request->input('recommend_status');
        $recommendation->setRateWeight($request->input('weight_rate',0));
        $object_data = $recommendation->data;
        if ($img_url) {
            $object_data['img'] = $img_url;
        }
        $object_data['title'] = $request->input('title');
        $recommendation->rate = $recommendation->getRateWeight() - $oldRate + $recommendation->rate;
        $recommendation->data = $object_data;
        $recommendation->save();
        if ($oldStatus != 1 && $recommendation->audit_status == 1) {
            $recommendation->setKeywordTags();
        }

        return $this->success(route('admin.operate.recommendRead.index'),'推荐修改成功');
    }

    public function verify(Request $request) {
        $ids = $request->input('ids');
        foreach ($ids as $id) {
            $recommendation = RecommendRead::find($id);
            $recommendation->setKeywordTags();
        }
        RecommendRead::whereIn('id',$ids)->update(['audit_status'=>1]);

        return $this->success(url()->previous(),'审核成功');
    }

    public function cancelVerify(Request $request) {
        $ids = $request->input('ids');
        RecommendRead::whereIn('id',$ids)->update(['audit_status'=>0]);

        return $this->success(url()->previous(),'取消推荐成功');
    }

    public function changeTags(Request $request) {
        $id = $request->input('id','');
        $tags = explode(',',$request->input('tagIds',0));
        $recommend = RecommendRead::find($id);
        $article = $recommend->source;
        $keywords = $article->data['keywords']??'';

        /*更新标签*/
        $oldTags = $article->tags->pluck('id')->toArray();
        foreach ($tags as $tag) {
            if (!in_array($tag,$oldTags)) {
                $article->tags()->attach($tag);
                $tagModel = Tag::find($tag);
                $keywords = $tagModel->name.','.$keywords;
            }
        }
        foreach ($oldTags as $oldTag) {
            if (!in_array($oldTag,$tags)) {
                $tagModel = Tag::find($oldTag);
                $keywords = str_replace($tagModel->name,'',$keywords);
                $keywords = str_replace(',,',',',$keywords);
                $article->tags()->detach($oldTag);
            }
        }
        if (isset($tagModel)) {
            $sData = $article->data;
            $sData['keywords'] = implode(',',array_unique(explode(',',$keywords)));
            $article->data = $sData;
            $article->save();
            $article->updateRelatedProducts();
        }

        $recommend->tags()->detach();
        $recommend->setKeywordTags();
        return response('success');
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
        return $this->success(url()->previous(),'推荐删除成功');
    }
}
