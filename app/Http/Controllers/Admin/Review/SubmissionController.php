<?php

namespace App\Http\Controllers\Admin\Review;

use App\Events\Frontend\System\OperationNotify;
use App\Http\Controllers\Admin\AdminController;
use App\Jobs\NewSubmissionJob;
use App\Logic\TagsLogic;
use App\Models\Groups\Group;
use App\Models\Question;
use App\Models\RecommendRead;
use App\Models\Submission;
use App\Models\Tag;
use App\Traits\SubmitSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SubmissionController extends AdminController
{
    use SubmitSubmission;
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

        if( isset($filter['status']) && $filter['status'] >=0 ){
            $query->where('status',$filter['status']);
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
        $this->validate($request, [
            'title' => 'required|between:1,6000',
            'tags' => 'required',
            'rate_star' => 'required',
            'author_id' => 'required'
        ]);

        $img_url = '';
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        }

        $data = [];
        $data['img'] = [$img_url];
        $data['category_ids'] = '';
        $data['author_identity'] = '';

        $data['current_address_name'] = $request->input('current_address_name');
        $data['current_address_longitude'] = $request->input('current_address_longitude');
        $data['current_address_latitude'] = $request->input('current_address_latitude');
        $data['mentions'] = is_array($request->input('mentions'))?array_unique($request->input('mentions')):[];

        $submission = Submission::create([
            'title'         => formatContentUrls($request->title),
            'slug'          => $this->slug($request->title),
            'type'          => 'review',
            'category_id'   => $request->input('tags'),
            'group_id'      => 0,
            'public'        => 1,
            'rate'          => firstRate(),
            'rate_star'     => $request->input('rate_star',0),
            'hide'          => $request->input('hide',0),
            'status'        => $request->input('status',0),
            'user_id'       => $request->input('author_id',0),
            'data'          => $data,
            'views'         => 1
        ]);

        $tagString = trim($request->input('tags'));
        \Log::info('test',[$tagString]);

        Tag::multiSaveByIds($tagString,$submission);
        if ($submission->status == 1) {
            $this->dispatch((new NewSubmissionJob($submission->id,true,'后台运营：'.formatSlackUser($request->user()).';')));
        }

        return $this->success(route('admin.review.submission.index'),'点评新建成功');
    }

}
