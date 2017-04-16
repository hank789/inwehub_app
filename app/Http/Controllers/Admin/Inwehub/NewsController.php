<?php

namespace App\Http\Controllers\Admin\Inwehub;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Inwehub\News;
use Illuminate\Http\Request;

use App\Http\Requests;

class NewsController extends AdminController
{

    /*新闻创建校验*/
    protected $validateRules = [
        'title' => 'required|min:5|max:255',
        'content_url' => 'required|max:255',
        'site_name' => 'required|max:255',
        'author' => 'required|max:255',
        'description' => 'max:200'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = News::query();

        /*话题过滤*/
        if( isset($filter['topic_id']) &&  $filter['topic_id'] > 0 ){
            $query->where('topic_id','=',$filter['topic_id']);
        }

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }

        /*提问时间过滤*/
        if( isset($filter['date_range']) && $filter['date_range'] ){
            $query->whereBetween('date_time',explode(" - ",$filter['date_range']));
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }


        $articles = $query->where('source_type',2)->orderBy('date_time','desc')->paginate(20);
        return view("admin.inwehub.news.index")->with('news',$articles)->with('filter',$filter);
    }



    public function create()
    {
        return view("admin.inwehub.news.create");
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
        $this->validateRules['content_url'] = 'required|max:255|unique:inwehub.news_info';

        $this->validate($request,$this->validateRules);

        $data = [
            'title'        => trim($request->input('title')),
            'content_url'           =>$request->input('content_url'),
            'mobile_url'   => $request->input('mobile_url')?:'',
            'author'  => $request->input('author'),
            'site_name'    => $request->input('site_name'),
            'description'  => $request->input('description')?:'',
            'date_time'    => date('Y-m-d H:i:s'),
            'topic_id'     => 0,
            'source_type'  => 2,
            'status'       => 1,
        ];


        $news = News::create($data);

        /*判断新闻是否添加成功*/
        if($news){
            $message = '发布成功! 请去给新闻挂载话题';
            return $this->success(route('admin.inwehub.news.index',['id'=>$news->_id]),$message);
        }

        return  $this->error("话题发布失败，请稍后再试",route('admin.inwehub.news.index'));

    }

    /**
     * 显示文字编辑页面
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $news = News::find($id);

        if(!$news){
            abort(404);
        }

        return view("admin.inwehub.news.edit")->with(compact('news'));

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
        $article_id = $request->input('id');
        $article = News::find($article_id);
        if(!$article){
            abort(404);
        }

        $request->flash();

        $this->validate($request,$this->validateRules);

        $article->title = trim($request->input('title'));
        $article->content_url = trim($request->input('content_url'));
        $article->site_name = trim($request->input('site_name'));
        $article->mobile_url = trim($request->input('mobile_url'))?:'';
        $article->author = trim($request->input('author'));
        $article->description = trim($request->input('description'))?:'';

        $article->save();

        return $this->success(route('admin.inwehub.news.index'),"新闻编辑成功");

    }


    /*文章审核*/
    public function verify(Request $request)
    {
        $articleIds = $request->input('id');
        News::whereIn('_id',$articleIds)->update(['status'=>1]);
        return $this->success(route('admin.inwehub.news.index'),'审核成功');

    }



    /**
     * 删除文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        News::whereIn('_id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.inwehub.news.index'),'禁用成功');
    }
}
