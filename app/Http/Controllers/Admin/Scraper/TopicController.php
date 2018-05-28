<?php

namespace App\Http\Controllers\Admin\Scraper;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Inwehub\News;
use App\Models\Inwehub\Topic;
use App\Models\Inwehub\WechatWenzhangInfo;
use Illuminate\Http\Request;

use App\Http\Requests;

class TopicController extends AdminController
{

    /*问题创建校验*/
    protected $validateRules = [
        'title' => 'required|min:5|max:500',
        'summary' => 'required|max:1024',
    ];


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();

        $query = Topic::query();


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
            $query->whereBetween('publish_date',explode(" - ",$filter['date_range']));
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }


        $articles = $query->orderBy('publish_date','desc')->paginate(20);
        return view("admin.inwehub.topic.index")->with('articles',$articles)->with('filter',$filter);
    }

    public function create()
    {
        return view("admin.inwehub.topic.create");
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
        if($request->user()->status === 0){
            return $this->error(route('website.index'),'操作失败！您的邮箱还未验证，验证后才能进行该操作！');
        }

        $request->flash();

        $this->validate($request,$this->validateRules);

        $data = [
            'user_id'      => $loginUser->id,
            'title'        => trim($request->input('title')),
            'summary'  => $request->input('summary'),
            'status'       => 0,
        ];


        $article = Topic::create($data);

        /*判断问题是否添加成功*/
        if($article){
            $message = '发布成功! 请去给话题添加相关新闻';
            return $this->success(route('admin.inwehub.topic.index',['id'=>$article->id]),$message);
        }

        return  $this->error("话题发布失败，请稍后再试",route('admin.inwehub.topic.index'));

    }

    /**
     * 显示文字编辑页面
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $article = Topic::find($id);

        if(!$article){
            abort(404);
        }
        $news = News::where('status',1)->Where(function ($query) use ($id){
            $query->where('topic_id',0)->orWhere('topic_id',$id);})->orderBy('date_time', 'asc')->paginate(50);

        return view("admin.inwehub.topic.edit")->with(compact('article','news','wehcat_articles'));
    }

    public function loadNews(Request $request){
        $id = $request->input('id');
        $search = $request->input('search');
        $start = $request->input('start');
        $length = $request->input('length');
        $selected = $request->input('selected');
        $word = $search['value'];
        $query = News::where('status',1)->Where(function ($query) use ($id){
            $query->where('topic_id',0)->orWhere('topic_id',$id);});

        if($word){
            $query->where('title','like',$word.'%');
        }
        $count = $query->count();
        $news = $query->orderBy('date_time', 'asc')->offset($start)->limit($length)->get();

        $data = [];
        $data['draw'] = $request->input('draw');
        $data['recordsTotal'] = $count;
        $data['recordsFiltered'] = $count;
        $ids = [];
        if($selected) {
            $selected = array_unique($selected);
        }
        foreach($news as $item){
            $o = [];
            $checked = (($item->topic_id == $id || ($selected && in_array($item->_id,$selected))) ? 'checked':'');
            $o[] = '<input type="checkbox" name="news[]" value="'.$item->_id.'" '.$checked.'/>';
            $o[] = $item->_id;
            $o[] = $item->title;
            $o[] = $item->date_time;
            $data['data'][] = $o;
            $ids[] = $item->_id;
        }

        if($selected){
            foreach($selected as $id){
                if(in_array($id,$ids)) continue;
                $item = News::find($id);
                $o = [];
                $o[] = '<input type="checkbox" name="news[]" value="'.$item->_id.'" checked/>';
                $o[] = $item->_id;
                $o[] = $item->title;
                $o[] = $item->date_time;
                $data['data'][] = $o;
            }
        }

        return response()->json($data);
    }

    /**
     * 话题添加新闻
     * @param $id
     * @param Request $request
     */
    public function topicNews(Request $request){
        $id = $request->input('id');
        $news = $request->input('news',array());

        News::where('topic_id',$id)->update(['topic_id'=>0]);
        if($news){
            News::whereIn('_id',$news)->update(['topic_id'=>$id]);
        }

        return $this->success(route('admin.inwehub.topic.index'),'添加新闻成功');
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
        $article = Topic::find($article_id);
        if(!$article){
            abort(404);
        }

        $request->flash();

        $this->validate($request,$this->validateRules);

        $article->title = trim($request->input('title'));
        $article->summary = $request->input('summary');

        $article->save();

        return $this->success(route('admin.inwehub.topic.index'),"编辑成功");

    }


    /*文章审核*/
    public function verify(Request $request)
    {
        $articleIds = $request->input('id');
        Topic::whereIn('id',$articleIds)->update(['status'=>1]);
        return $this->success(route('admin.inwehub.topic.index'),'审核成功');

    }



    /**
     * 删除文章
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Topic::destroy($request->input('id'));
        return $this->success(route('admin.inwehub.topic.index'),'删除成功');
    }
}