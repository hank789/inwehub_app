<?php

namespace App\Http\Controllers\Admin;

use App\Models\RecommendRead;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SubmissionController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Submission::query();

        /*提问人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }

        $submissions = $query->orderBy('id','desc')->paginate(20);
        return view("admin.operate.article.index")->with('submissions',$submissions)->with('filter',$filter);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $submission = Submission::find($id);
        return view("admin.operate.article.edit")->with('submission',$submission);
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
        $submission = Submission::find($id);
        if(!$submission){
            return $this->error(route('admin.operate.article.index'),'文章不存在，请核实');
        }
        $img_url = '';
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        } elseif (empty($submission->data['img'])) {
            return $this->error(route('admin.operate.article.edit',['id'=>$id]),'请上传文章封面图片');
        }
        $author_id = $request->input('author_id',-1);
        if ($author_id != -1) {
            $submission->author_id = $author_id;
        }

        $object_data = $submission->data;
        if ($img_url) {
            $object_data['img'] = $img_url;
        }
        $submission->data = $object_data;
        $submission->save();

        return $this->success(route('admin.operate.article.index'),'文章修改成功');
    }



    /*文章推荐精选审核*/
    public function verifyRecommend(Request $request)
    {
        $articleIds = $request->input('ids');
        foreach ($articleIds as $articleId) {
            $article = Submission::find($articleId);
            RecommendRead::firstOrCreate([
                'source_id' => $articleId,
                'source_type' => get_class($article)
            ],[
                'source_id' => $articleId,
                'source_type' => get_class($article),
                'sort' => 0,
                'audit_status' => 0,
                'read_type' => RecommendRead::READ_TYPE_SUBMISSION,
                'data' => array_merge([
                    'title' => $article->title,
                    'img'   => $article->data['img'],
                    'category_id' => $article->category_id,
                    'category_name' => $article->category_name,
                    'type' => $article->type,
                    'slug' => $article->slug
                ],$article->data)
            ]);
        }
        return $this->success(route('admin.operate.article.index'),'设为精选成功');

    }

    /**
     * 删除文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('ids');
        Submission::whereIn('id',$ids)->delete();
        return $this->success(route('admin.operate.article.index'),'删除成功');
    }
}
