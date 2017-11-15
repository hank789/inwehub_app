<?php

namespace App\Http\Controllers\Admin;

use App\Models\Question;
use App\Models\RecommendRead;
use Illuminate\Http\Request;

use App\Http\Requests;

class QuestionController extends AdminController
{
    /**
     * 问题列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Question::query();

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

        if( isset($filter['is_hot']) && $filter['is_hot']> 0 ){
            $query->where('is_hot','=',1);
        }

        if( isset($filter['is_recommend']) && $filter['is_recommend']> 0 ){
            $query->where('is_recommend','=',1);
        }

        $questions = $query->orderBy('created_at','desc')->paginate(20);
        return view("admin.question.index")->with('questions',$questions)->with('filter',$filter);
    }


    /**
     * 显示问题编辑页面
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


    /*问题审核*/
    public function verify(Request $request)
    {
        $questionIds = $request->input('id');
        Question::whereIn('id',$questionIds)->update(['status'=>1]);
        return $this->success(route('admin.question.index').'?status=0','问题审核成功');

    }

    /*设为精选推荐*/
    public function verifyRecommendHeart(Request $request)
    {
        $questionIds = $request->input('id');
        foreach ($questionIds as $questionId) {
            $question = Question::find($questionId);
            RecommendRead::create([
                'source_id' => $questionId,
                'source_type' => get_class($question),
                'sort' => 0,
                'audit_status' => 0,
                'read_type' => RecommendRead::READ_TYPE_QUESTION,
                'data' => [
                    'title' => $question->title,
                    'img'   => ''
                ]
            ]);
        }
        return $this->success(route('admin.question.index'),'设为精选成功');

    }

    /*设为推荐*/
    public function verifyRecommend(Request $request)
    {
        $questionIds = $request->input('id');
        Question::where('status','>=',6)->whereIn('id',$questionIds)->update(['is_recommend'=>1]);
        return $this->success(route('admin.question.index'),'设为推荐成功');

    }

    /*取消推荐*/
    public function cancelRecommend(Request $request)
    {
        $questionIds = $request->input('id');
        Question::whereIn('id',$questionIds)->update(['is_recommend'=>0]);
        return $this->success(route('admin.question.index'),'取消推荐成功');

    }


    /*设为热门*/
    public function verifyHot(Request $request)
    {
        $questionIds = $request->input('id');
        Question::where('status','>=',6)->whereIn('id',$questionIds)->update(['is_hot'=>1]);
        return $this->success(route('admin.question.index'),'设为热门成功');

    }

    /*取消热门*/
    public function cancelHot(Request $request)
    {
        $questionIds = $request->input('id');
        Question::whereIn('id',$questionIds)->update(['is_hot'=>0]);
        return $this->success(route('admin.question.index'),'取消热门成功');

    }


    /*修改分类*/
    public function changeCategories(Request $request){
        $ids = $request->input('ids','');
        $categoryId = $request->input('category_id',0);
        if($ids){
            Question::whereIn('id',explode(",",$ids))->update(['category_id'=>$categoryId]);
        }
        return $this->success(route('admin.question.index'),'分类修改成功');
    }

    /**
     * 删除问题
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $questionIds = $request->input('id');
        Question::destroy($questionIds);
        return $this->success(route('admin.question.index'),'问题删除成功');
    }
}
