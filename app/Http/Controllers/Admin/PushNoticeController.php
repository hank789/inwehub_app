<?php

namespace App\Http\Controllers\Admin;

use App\Models\PushNotice;
use App\Models\Readhub\Submission;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Config;

class PushNoticeController extends AdminController
{

    /**
     * 显示推荐列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notices = PushNotice::orderBy('id','desc')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.operate.push_notice.index')->with('notices',$notices);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $notice = PushNotice::findOrNew(0);
        $notice->id = 0;
        return view('admin.operate.push_notice.edit')->with('notice',$notice);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $notice = PushNotice::find($id);
        if(!$notice){
            return $this->error(route('admin.operate.pushNotice.index'),'不存在，请核实');
        }
        return view('admin.operate.push_notice.edit')->with('notice',$notice);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->flash();

        $validateRules = [
            'id'      => 'required',
            'title'   => 'required',
            'url' => 'required|url',
            'notification_type' => 'required|integer',
            'is_push'           => 'required|integer'
        ];
        $this->validate($request,$validateRules);

        $data = [
            'title' => $request->input('title'),
            'url'   => $request->input('url'),
            'notification_type'   => $request->input('notification_type'),
        ];

        $notice = PushNotice::updateOrCreate(['id'=>$request->get('id')],$data);

        return $this->success(route('admin.operate.pushNotice.index'),'成功');
    }

    public function verify(Request $request) {

    }

    public function testPush(Request $request) {

    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Submission::whereIn('id',$request->input('ids'))->update(['recommend_status'=>Submission::RECOMMEND_STATUS_NOTHING]);
        return $this->success(route('admin.operate.recommendRead.index'),'推荐删除成功');
    }
}
