<?php

namespace App\Http\Controllers\Admin;

use App\Models\Answer;
use App\Models\RecommendRead;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;

class AnswerController extends AdminController
{
    /**
     * 回答列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Answer::query();

        /*提问人过滤*/
        if( isset($filter['user_id']) &&  $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        /*问题过滤*/
        if( isset($filter['question_id']) &&  $filter['question_id'] > 0 ){
            $query->where('question_id','=',$filter['question_id']);
        }

        /*提问时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('created_at',explode(" - ",$filter['date_range']));
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }
        $answers = $query->orderBy('created_at','desc')->paginate(20);
        return view("admin.answer.index")->with('answers',$answers)->with('filter',$filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

    /*回答审核*/
    public function verify(Request $request)
    {
        $answerIds = $request->input('id');
        Answer::whereIn('id',$answerIds)->update(['status'=>1]);
        return $this->success(route('admin.answer.index').'?status=0','回答审核成功');
    }

    /*设为精选推荐*/
    public function verifyRecommendHeart(Request $request)
    {
        $ids = $request->input('id');
        foreach ($ids as $id) {
            $answer = Answer::find($id);
            if ($answer->question->question_type == 1) continue;
            RecommendRead::firstOrCreate([
                'source_id' => $id,
                'source_type' => get_class($answer),
            ],[
                'source_id' => $id,
                'source_type' => get_class($answer),
                'sort' => 0,
                'audit_status' => 0,
                'rate' => $answer->question->rate,
                'created_at' => $answer->created_at,
                'updated_at' => Carbon::now(),
                'read_type' => RecommendRead::READ_TYPE_FREE_QUESTION_ANSWER,
                'data' => [
                    'title' => $answer->question->title,
                    'img'   => ''
                ]
            ]);
        }
        return $this->success(url()->previous(),'设为精选成功');

    }

    /**
     *删除回答
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $ids = $request->input('id');
        foreach ($ids as $id) {
            $answer = Answer::find($id);
            if ($answer->question->question_type == 1) return $this->error(route('admin.answer.index'),'专业问答不能删除');
            $answer->delete();
        }
        return $this->success(url()->previous(),'回答删除成功');
    }
}
