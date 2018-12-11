<?php

namespace App\Http\Controllers\Admin\Scraper;

use App\Http\Controllers\Admin\AdminController;
use App\Jobs\JobToSubmission;
use App\Models\Scraper\Jobs;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Logic\TagsLogic;

class JobsController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Jobs::query();

        /*标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }

        /*状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        } else {
            $filter['status']=1;
            $query->where('status','=',1);
        }

        $articles = $query->orderBy('id','desc')->paginate(20);
        $data = TagsLogic::loadTags(6,'','id');
        $tags = $data['tags'];
        return view("admin.scraper.jobs.index")->with('articles',$articles)->with('filter',$filter)->with('tags',$tags);
    }

    public function publish(Request $request) {
        $this->validate($request, [
            'ids' => 'required',
        ]);
        $ids = $request->input('ids');
        foreach ($ids as $id) {
            $article = Jobs::find($id);
            if ($article->status == 2) continue;
            if ($article->status == 3) {
                $article->status = 1;
                $article->save();
            }
            dispatch(new JobToSubmission($id));
        }
        return $this->success(url()->previous(),'成功');
    }

    public function setSupportType(Request $request) {
        $this->validate($request, [
            'id' => 'required',
            'support_type' => 'required|in:1,2,3,4',
        ]);
        RateLimiter::instance()->hSet('job_support_type',$request->input('id'),$request->input('support_type'));
        return $this->success(url()->previous(),'成功');
    }



    /*文章推荐精选审核*/
    public function verifyRecommend(Request $request)
    {
        $articleId = $request->input('id');
        $title = $request->input('title');
        $tagsId = $request->input('tagIds',0);
        $tips = $request->input('tips');
        dispatch(new ArticleToRecommend($articleId,$title,$tagsId,$tips));
        return $this->success(url()->previous(),'设为精选成功');

    }

    /**
     * 删除文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('ids');
        if ($ids) {
            $ids = explode(',',$ids);
            Jobs::whereIn('id',$ids)->where('status',1)->update(['status'=>3]);
        }
        return $this->success(url()->previous(),'成功');
    }

}
