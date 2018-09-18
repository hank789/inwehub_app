<?php

namespace App\Http\Controllers\Admin\Scraper;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Groups\Group;
use App\Models\Scraper\Feeds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class FeedsController extends AdminController
{

    /*新闻创建校验*/
    protected $validateRules = [
        'name'        => 'required|max:255',
        'group_id'    => 'required|integer|min:1',
        'user_id'     => 'required|integer|min:1',
        'source_type' => 'required|max:255|in:1,2',
        'source_link' => 'required|max:255',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Feeds::query();

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', '%'.$filter['word'].'%');
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }


        $articles = $query->orderBy('created_at','desc')->paginate(20);
        return view("admin.scraper.feeds.index")->with('feeds',$articles)->with('filter',$filter);
    }



    public function create()
    {
        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->get()->toArray();
        return view("admin.scraper.feeds.create")->with('groups',$groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->flash();

        $this->validateRules['name'] = 'required|max:255|unique:scraper_feeds';

        $this->validate($request,$this->validateRules);
        $data = [
            'name'        => trim($request->input('name')),
            'group_id'  =>$request->input('group_id'),
            'user_id'   => $request->input('user_id',504),
            'source_type' => $request->input('source_type'),
            'source_link'   => $request->input('source_link'),
            'keywords'  => trim($request->input('keywords')),
            'is_auto_publish' => $request->input('is_auto_publish',0),
            'status'       => 0,
        ];

        $news = Feeds::create($data);

        /*判断新闻是否添加成功*/
        if($news){
            $message = '发布成功! ';
            return $this->success(route('admin.scraper.feeds.index'),$message);
        }

        return  $this->error("发布失败，请稍后再试",route('admin.scraper.feeds.index'));

    }

    /**
     * 显示文字编辑页面
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $feeds = Feeds::find($id);

        if(!$feeds){
            abort(404);
        }
        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->get()->toArray();
        return view("admin.scraper.feeds.edit")->with(compact('feeds','groups'));

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
        $feed_id = $request->input('id');
        $feed = Feeds::find($feed_id);
        if(!$feed){
            abort(404);
        }

        $request->flash();

        $this->validate($request,$this->validateRules);

        $feed->name = trim($request->input('name'));
        $feed->group_id = trim($request->input('group_id'));
        $feed->user_id = $request->input('user_id',504);
        $feed->source_link = trim($request->input('source_link'));
        $feed->source_type = $request->input('source_type');
        $feed->keywords = trim($request->input('keywords',''));
        $feed->is_auto_publish = $request->input('is_auto_publish',0);

        $feed->save();

        return $this->success(route('admin.scraper.feeds.index'),"编辑成功");

    }

    /**
     * 删除
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Feeds::whereIn('id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.scraper.feeds.index'),'禁用成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $articleIds = $request->input('id');
        Feeds::whereIn('id',$articleIds)->update(['status'=>1]);
        return $this->success(route('admin.scraper.feeds.index'),'审核成功,稍后会自动抓取数据');

    }

    public function sync(Request $request){
        $id = $request->input('id');
        $feed = Feeds::find($id);
        switch($feed->source_type){
            case 1:
                Artisan::queue('scraper:rss',['id'=>$id]);
                break;
            case 2:
                Artisan::queue('scraper:atom',['id'=>$id]);
                break;
        }
        return $this->success(route('admin.scraper.feeds.index'),"正在抓取数据,稍等片刻");

    }

}
