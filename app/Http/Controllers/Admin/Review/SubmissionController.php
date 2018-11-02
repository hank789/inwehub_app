<?php

namespace App\Http\Controllers\Admin\Review;

use App\Events\Frontend\System\OperationNotify;
use App\Http\Controllers\Admin\AdminController;
use App\Logic\TagsLogic;
use App\Models\Groups\Group;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\Tag;
use Carbon\Carbon;
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

        $query = Submission::where('type','review');

        /*提问人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('data','like', '%'.$filter['word'].'%');
        }
        if (isset($filter['sortByRate']) && $filter['sortByRate']) {
            $query->orderBy('rate','desc');
        }

        $submissions = $query->orderBy('id','desc')->paginate(20);
        $data = TagsLogic::loadTags(6,'','id');
        $tags = $data['tags'];
        return view("admin.review.submission.index")->with('submissions',$submissions)->with('filter',$filter)->with('tags',$tags);
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
        return view("admin.review.submission.edit")->with('submission',$submission);
    }

    public function create($id) {
        $tag = Tag::find($id);
        return view('admin.review.submission.create')->with('tag',$tag);
    }

    public function store(Request $request) {

    }

}
