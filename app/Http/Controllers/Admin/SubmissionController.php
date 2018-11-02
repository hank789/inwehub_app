<?php

namespace App\Http\Controllers\Admin;

use App\Events\Frontend\System\OperationNotify;
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

        $query = Submission::query();

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
        return view("admin.operate.article.index")->with('submissions',$submissions)->with('filter',$filter)->with('tags',$tags);
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
        $related_question = $request->input('related_question',0);
        if ($related_question) {
            Question::findOrFail($related_question);
        }
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        }
        $author_id = $request->input('author_id',-1);
        if ($author_id != -1) {
            $submission->author_id = $author_id;
        }

        $object_data = $submission->data;
        if ($img_url) {
            $object_data['img'] = $img_url;
        }
        if ($related_question) {
            $object_data['related_question'] = $related_question;
        }
        $submission->title = $request->input('title');
        $rate_star = $request->input('rate_star',-1);
        if ($rate_star >= 0) {
            $submission->rate_star = $rate_star;
        }
        $submission->data = $object_data;
        $submission->save();

        $tagString = trim($request->input('tags'));
        \Log::info('test',[$tagString]);

        /*更新标签*/
        $oldTags = $submission->tags->pluck('id')->toArray();
        if (!is_array($tagString)) {
            $tags = array_unique(explode(",",$tagString));
        } else {
            $tags = array_unique($tagString);
        }
        foreach ($tags as $tag) {
            if (!in_array($tag,$oldTags)) {
                $submission->tags()->attach($tag);
            }
        }
        foreach ($oldTags as $oldTag) {
            if (!in_array($oldTag,$tags)) {
                $submission->tags()->detach($oldTag);
            }
        }

        return $this->success(url()->previous(),'文章修改成功');
    }

    public function setSupportType(Request $request) {
        $this->validate($request, [
            'id' => 'required',
            'support_type' => 'required|in:1,2,3,4',
        ]);
        $submission = Submission::find($request->input('id'));
        $submission->support_type = $request->input('support_type');
        $submission->save();
        return $this->success(url()->previous(),'成功');
    }

    //设为优质内容
    public function setGood(Request $request) {
        $articleId = $request->input('id');
        $article = Submission::find($articleId);
        if ($article->is_recommend) {
            $article->is_recommend = 0;
        } else {
            $article->is_recommend = 1;
        }
        $article->save();
        if ($article->is_recommend) {
            return response('failed');
        }
        return response('success');
    }



    /*文章推荐精选审核*/
    public function verifyRecommend(Request $request)
    {
        $articleId = $request->input('id');
        $title = $request->input('title');
        $tagsId = $request->input('tagIds',0);
        $article = Submission::find($articleId);
        $group = Group::find($article->group_id);
        if (!$group->public) return $this->error(route('admin.operate.article.index'),'私有圈子里的文章不能设为推荐');
        $oldData = $article->data;
        unset($oldData['description']);
        unset($oldData['title']);
        $recommend = RecommendRead::firstOrCreate([
            'source_id' => $articleId,
            'source_type' => get_class($article)
        ],[
            'source_id' => $articleId,
            'source_type' => get_class($article),
            'tips' => $request->input('tips'),
            'sort' => 0,
            'audit_status' => 0,
            'rate' => $article->rate,
            'read_type' => RecommendRead::READ_TYPE_SUBMISSION,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'data' => array_merge($oldData, [
                'title' => $title?:$article->title,
                'img'   => $article->data['img'],
                'category_id' => $article->category_id,
                'category_name' => $article->category_name,
                'type' => $article->type,
                'slug' => $article->slug,
                'group_id' => $article->group_id
            ])
        ]);
        if ($recommend->audit_status == 0) {
            $recommend->audit_status = 1;
            $recommend->sort = $recommend->id;
            $recommend->save();
            Tag::multiAddByIds($tagsId,$article);
            $recommend->setKeywordTags();
            $slackFields = [];
            $slackFields[] = [
                'title'=>'链接',
                'value'=>config('app.mobile_url').'#/c/'.$article->category_id.'/'.$article->slug
            ];
            event(new OperationNotify('用户'.formatSlackUser($request->user()).'新增精选['.$recommend->data['title'].']',$slackFields));
        }
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
            Submission::destroy($ids);
        }
        return $this->success(url()->previous(),'删除成功');
    }
}
