<?php

namespace App\Http\Controllers\Admin\Inwehub;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Inwehub\Feeds;
use App\Models\Inwehub\News;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Artisan;

class FeedsController extends AdminController
{

    /*新闻创建校验*/
    protected $validateRules = [
        'name'        => 'required|max:255',
        'description' => 'required|max:255',
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
        return view("admin.inwehub.feeds.index")->with('feeds',$articles)->with('filter',$filter);
    }



    public function create()
    {
        return view("admin.inwehub.feeds.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $loginUser = $request->user();

        $request->flash();

        $this->validateRules['name'] = 'required|max:255|unique:inwehub.feeds';

        $this->validate($request,$this->validateRules);

        $source_type = $request->input('source_type');


        $data = [
            'user_id'      => $loginUser->id,
            'name'        => trim($request->input('name')),
            'description'  =>$request->input('description'),
            'source_type' => $request->input('source_type'),
            'source_link'   => $request->input('source_link'),
            'status'       => 1,
        ];


        $news = Feeds::create($data);

        /*判断新闻是否添加成功*/
        if($news){
            $message = '发布成功! ';
            return $this->success(route('admin.inwehub.feeds.index'),$message);
        }

        return  $this->error("发布失败，请稍后再试",route('admin.inwehub.feeds.index'));

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

        return view("admin.inwehub.feeds.edit")->with(compact('feeds'));

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
        $feed->description = trim($request->input('description'));
        $feed->source_link = trim($request->input('source_link'));
        $feed->source_type = $request->input('source_type');

        $feed->save();

        return $this->success(route('admin.inwehub.feeds.index'),"编辑成功");

    }

    /**
     * 删除
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Feeds::destroy($request->input('id'));
        return $this->success(route('admin.inwehub.feeds.index'),'删除成功');
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
        return $this->success(route('admin.inwehub.feeds.index'),"正在抓取数据,稍等片刻");

    }

}
