<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\CloseActivity;
use App\Models\Article;
use App\Models\Category;
use App\Models\RecommendRead;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class ArticleController extends AdminController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Article::query();

        $filter['category_id'] = $request->input('category_id',-1);


        /*提问人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }

        /*提问时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('created_at',explode(" - ",$filter['date_range']));
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }

        /*分类过滤*/
        if( $filter['category_id']> 0 ){
            $query->where('category_id','=',$filter['category_id']);
        }
        $recommend_home_ac = Redis::connection()->hgetall('recommend_home_ac');

        $articles = $query->orderBy('created_at','desc')->paginate(20);
        return view("admin.article.index")->with('articles',$articles)->with('filter',$filter)->with('recommend_home_ac',$recommend_home_ac);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }


    /*文章审核*/
    public function verify(Request $request)
    {
        $articleIds = $request->input('id');
        Article::whereIn('id',$articleIds)->update(['status'=>1]);
        foreach ($articleIds as $articleId) {
            $article = Article::find($articleId);
            if ($article->deadline) {
                $this->dispatch((new CloseActivity($articleId))->delay(Carbon::createFromTimestamp(strtotime($article->deadline))));
            }
        }
        return $this->success(route('admin.article.index').'?status=0','活动审核成功');
    }

    /*推荐精选审核*/
    public function verifyRecommend(Request $request)
    {
        $articleIds = $request->input('id');
        foreach ($articleIds as $articleId) {
            $article = Article::find($articleId);
            $category = Category::find($article->category_id);

            RecommendRead::create([
                'source_id' => $articleId,
                'source_type' => get_class($article),
                'sort' => 0,
                'audit_status' => 0,
                'read_type' => $category->slug == 'activity_enroll' ? RecommendRead::READ_TYPE_ACTIVITY : RecommendRead::READ_TYPE_PROJECT_OPPORTUNITY,
                'data' => [
                    'title' => $article->title,
                    'img'   => $article->logo
                ]
            ]);
        }
        return $this->success(route('admin.article.index'),'设为精选成功');

    }

    /*修改分类*/
    public function changeCategories(Request $request){
        $ids = $request->input('ids','');
        $categoryId = $request->input('category_id',0);
        if($ids){
            Article::whereIn('id',explode(",",$ids))->update(['category_id'=>$categoryId]);
        }
        return $this->success(route('admin.article.index'),'分类修改成功');
    }



    /**
     * 删除文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('id');
        Article::where('status',Article::ARTICLE_STATUS_PENDING)->whereIn('id',$ids)->delete();
        return $this->success(route('admin.article.index'),'活动删除成功');
    }
}
