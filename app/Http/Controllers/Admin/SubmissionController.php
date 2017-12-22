<?php

namespace App\Http\Controllers\Admin;

use App\Models\RecommendRead;
use App\Models\Submission;
use Illuminate\Http\Request;

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
