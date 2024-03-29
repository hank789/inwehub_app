<?php

namespace App\Http\Controllers\Admin\Review;

use App\Http\Controllers\Admin\AdminController;
use App\Jobs\NewSubmissionJob;
use App\Logic\TagsLogic;
use App\Models\Comment;
use App\Models\Submission;
use App\Models\Tag;
use App\Traits\SubmitSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Excel;

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

        if( isset($filter['tags']) && $filter['tags'] >0 ){
            $query->where('category_id',$filter['tags']);
            $tag = Tag::find($filter['tags']);
            $filter['tags'] = [$tag];
        }

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('data','like', '%'.$filter['word'].'%');
        }
        if (isset($filter['showWeapp']) && $filter['showWeapp']) {
            $query->where('data','like','%weapp_dianping%');
        }
        if (isset($filter['sortByRate']) && $filter['sortByRate']) {
            $query->orderBy('rate','desc');
        }

        $submissions = $query->orderBy('id','desc')->paginate(20);
        $data = TagsLogic::loadTags(6,'','id');
        $tags = $data['tags'];
        return view("admin.review.submission.index")->with('submissions',$submissions)->with('filter',$filter)->with('tags',$tags);
    }

    public function export(Request $request) {
        $query = Submission::where('type','review');

        $cellData = [];
        $cellData[] = ['ID','标题','评分','产品'];
        $page = 1;
        $submissions = $query->orderBy('id','desc')->simplePaginate(100,['*'],'page',$page);
        while ($submissions->count() > 0) {
            foreach ($submissions as $submission) {
                $cellData[] = [
                    $submission->id,
                    $submission->data['origin_title'],
                    $submission->rate_star,
                    implode(',',$submission->tags->pluck('name')->toArray())
                ];
            }
            $page ++;
            $submissions = $query->orderBy('id','desc')->simplePaginate(100,['*'],'page',$page);
        }
        Excel::create('点评',function($excel) use ($cellData){
            $excel->sheet('score', function($sheet) use ($cellData){
                $sheet->rows($cellData);
            });
        })->export('xlsx');
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

        Tag::multiSaveByIds($tagString,$submission);
        if ($submission->status == 1) {
            $this->dispatch((new NewSubmissionJob($submission->id,true,'后台运营：'.formatSlackUser($request->user()).';')));
        }

        return $this->success(route('admin.review.submission.index'),'点评新建成功');
    }

    public function addOfficialReply(Request $request,$id) {
        $submission = Submission::find($id);
        $comment = Comment::where('source_id',$id)->where('source_type',get_class($submission))
            ->where('comment_type',Comment::COMMENT_TYPE_OFFICIAL)->first();
        return view("admin.review.submission.addOfficialReply")->with('submission',$submission)->with('comment',$comment);
    }

    public function storeOfficialReply(Request $request,$id) {
        $submission = Submission::find($id);
        $comment_id = $request->input('comment_id',0);
        $data = [
            'content'          => $request->input('content'),
            'user_id'       => $request->user()->id,
            'parent_id'     => 0,
            'level'         => 0,
            'source_id' => $submission->id,
            'source_type' => get_class($submission),
            'to_user_id'  => 0,
            'supports'    => 0,
            'comment_type' => Comment::COMMENT_TYPE_OFFICIAL,
            'mentions' => [],
            'status' => $request->input('status',0)
        ];
        if (!$comment_id) {
            $comment = Comment::create($data);
        } else {
            $comment = Comment::find($comment_id);
            $comment->content = $data['content'];
            $comment->status = $request->input('status',0);
            $comment->save();
        }
        return $this->success(route('admin.review.submission.index'),'官方回复成功');
    }

}
