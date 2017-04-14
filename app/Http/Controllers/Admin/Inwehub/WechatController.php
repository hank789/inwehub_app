<?php

namespace App\Http\Controllers\Admin\Inwehub;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Inwehub\Feeds;
use App\Models\Inwehub\News;
use App\Models\Inwehub\WechatMpInfo;
use App\Models\Inwehub\WechatMpList;
use App\Models\Inwehub\WechatWenzhangInfo;
use Illuminate\Http\Request;

use App\Http\Requests;

class WechatController extends AdminController
{

    /*新闻创建校验*/
    protected $validateRules = [
        'wx_hao'        => 'required|max:255'
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexAuthor(Request $request)
    {
        $filter =  $request->all();

        $query = WechatMpInfo::query();

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', '%'.$filter['word'].'%');
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }


        $authors = $query->orderBy('create_time','desc')->paginate(20);
        return view("admin.inwehub.wechat.author.index")->with('authors',$authors)->with('filter',$filter);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexArticle(Request $request)
    {
        $filter =  $request->all();

        $query = WechatWenzhangInfo::query();

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }
        /*话题过滤*/
        if( isset($filter['topic_id']) &&  $filter['topic_id'] > 0 ){
            $query->where('topic_id','=',$filter['topic_id']);
        }

        $articles = $query->orderBy('date_time','desc')->paginate(20);
        return view("admin.inwehub.wechat.article.index")->with('articles',$articles)->with('filter',$filter);
    }

    /*审核*/
    public function verifyAuthor(Request $request)
    {
        $articleIds = $request->input('id');
        WechatMpInfo::whereIn('_id',$articleIds)->update(['status'=>1]);
        return $this->success(route('admin.inwehub.wechat.author.index').'?status=0','审核成功');
    }


    public function createAuthor()
    {
        return view("admin.inwehub.wechat.author.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAuthor(Request $request)
    {

        $request->flash();

        $this->validateRules['wx_hao'] = 'required|max:255|unique:inwehub.wechat_add_mp_list';

        $this->validate($request,$this->validateRules);

        $data = [
            'wx_hao'        => trim($request->input('wx_hao')),
            'name'  =>$request->input('wx_hao'),
        ];

        $news = WechatMpList::create($data);

        if($news){
            $message = '发布成功!请稍等片刻,正在为您抓取公众号信息 ';
            return $this->success(route('admin.inwehub.wechat.author.index'),$message);
        }

        return  $this->error("发布失败，请稍后再试",route('admin.inwehub.wechat.author.index'));

    }

    /**
     * 删除
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyAuthor(Request $request)
    {
        WechatMpInfo::whereIn('_id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.inwehub.wechat.author.index'),'禁用成功');
    }
}
