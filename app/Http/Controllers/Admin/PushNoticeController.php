<?php

namespace App\Http\Controllers\Admin;

use App\Models\PushNotice;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Notifications\PushNotice as PushNoticeNotification;

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
            'url' => 'required',
            'notification_type' => 'required|integer',
        ];
        $this->validate($request,$validateRules);

        switch ($request->input('notification_type')) {
            case PushNotice::PUSH_NOTIFICATION_TYPE_READHUB:
                $recommendation = Submission::where('id',$request->input('url'))->first();;
                if(!$recommendation){
                    return $this->error(route('admin.operate.pushNotice.edit',['id'=>$request->get('id')]),'推荐不存在，请核实');
                }
                if ($recommendation->type == 'text') {
                    return $this->error(route('admin.operate.pushNotice.edit',['id'=>$request->get('id')]),'该动态非外联，请选择"app内页"项');
                }
                break;
        }

        $notice = PushNotice::find($request->get('id'));
        $status = PushNotice::PUSH_STATUS_DRAFT;
        if ($notice) {
            $status = $notice->status;
        }
        $data = [
            'title' => $request->input('title'),
            'url'   => $request->input('url'),
            'status' => $status,
            'notification_type'   => $request->input('notification_type'),
        ];

        PushNotice::updateOrCreate(['id'=>$request->get('id')],$data);

        return $this->success(route('admin.operate.pushNotice.index'),'成功');
    }

    public function verify(Request $request) {
        $validateRules = [
            'push_id'   => 'required',
        ];
        $this->validate($request,$validateRules);
        $push = PushNotice::findOrFail($request->input('push_id'));
        if ($push->status != PushNotice::PUSH_STATUS_TESTED) {
            return $this->error(route('admin.operate.pushNotice.index'),'请先测试推送无误再发送给所有用户');
        }
        $users = User::where('status',1)->get();
        $push->status = PushNotice::PUSH_STATUS_SEND;
        $push->save();
        foreach ($users as $user) {
            $user->notify(new PushNoticeNotification($push,$user->id));
        }
        return $this->success(route('admin.operate.pushNotice.index'),'发送成功，请留意推送信息');
    }

    public function testPush(Request $request) {
        $validateRules = [
            'test_push_user_id'      => 'required',
            'test_push_id'   => 'required',
        ];
        $this->validate($request,$validateRules);
        $push = PushNotice::findOrFail($request->input('test_push_id'));
        $user = User::findOrFail($request->input('test_push_user_id'));
        $user->notify(new PushNoticeNotification($push,$user->id));
        $push->status = PushNotice::PUSH_STATUS_TESTED;
        $push->save();
        return $this->success(route('admin.operate.pushNotice.index'),'测试发送成功，请留意推送信息');
    }

    /**
     * 删除推荐
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        PushNotice::where('status','!=', PushNotice::PUSH_STATUS_SEND)->whereIn('id',$request->input('ids'))->delete();
        return $this->success(url()->previous(),'推送删除成功');
    }
}
