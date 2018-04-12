<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mockery\Matcher\Not;

class NoticeController extends AdminController
{

    /*权限验证规则*/
    protected $validateRules = [
        'subject' => 'required|max:255',
        'url' => 'required|max:255',
        'img_url' => 'required',
        'sort' => 'required'
    ];


    /**
     * 显示公告列表
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notices = Notice::orderBy('updated_at','DESC')->paginate(Config::get('inwehub.admin.page_size'));
        return view('admin.notice.index')->with('notices',$notices);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $notice = Notice::orderBy('sort','DESC')->first();
        $sort = 1;
        if($notice){
            $sort = $notice->sort + 1;
        }
        return view('admin.notice.create')->with('sort',$sort);
    }



    /**
     * 保存添加的公告信息
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->flash();
        $this->validate($request,$this->validateRules);
        $img_url = '';
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'notices/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        } else {
            return $this->error(route('admin.notice.create'),'请上传封面图片');
        }
        $data = $request->all();
        $data['img_url'] = $img_url;
        Notice::create($data);
        return $this->success(route('admin.notice.index'),'公告添加成功');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
        $notice = Notice::find($id);
        if(!$notice){
            return $this->error(route('admin.notice.index'),'公告不存在，请核实');
        }
        return view('admin.notice.edit')->with('notice',$notice);
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
        $request->flash();
        $notice = Notice::find($id);
        if(!$notice){
            return $this->error(route('admin.notice.index'),'公告不存在，请核实');
        }
        $this->validate($request,$this->validateRules);
        $img_url = '';
        if($request->hasFile('img_url')){
            $file = $request->file('img_url');
            $extension = strtolower($file->getClientOriginalExtension());
            $filePath = 'notices/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        }
        $notice->subject = $request->input('subject');
        $notice->url = $request->input('url');
        if ($img_url) $notice->img_url = $img_url;
        $notice->sort = $request->input('sort');
        $notice->status = $request->input('status');
        $notice->save();
        return $this->success(route('admin.notice.index'),'公告修改成功');
    }

    /**
     * 删除公告
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Notice::destroy($request->input('ids'));
        return $this->success(route('admin.notice.index'),'公告删除成功');
    }
}
