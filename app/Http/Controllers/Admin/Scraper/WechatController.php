<?php

namespace App\Http\Controllers\Admin\Scraper;

use App\Http\Controllers\Admin\AdminController;
use App\Jobs\ArticleToSubmission;
use App\Models\Scraper\Feeds;
use App\Models\Scraper\News;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatMpList;
use App\Models\Scraper\WechatWenzhangInfo;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Artisan;

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
        return view("admin.scraper.wechat.author.index")->with('authors',$authors)->with('filter',$filter);
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

        if( isset($filter['news_id']) &&  $filter['news_id'] > 0 ){
            $query->where('_id','=',$filter['news_id']);
        }

        /*公众号id过滤*/
        if( isset($filter['user_id']) && $filter['user_id'] > -1 ){
            $query->where('mp_id','=',$filter['user_id']);
        }

        $articles = $query->where('source_type',1)->orderBy('date_time','desc')->paginate(20);
        return view("admin.scraper.wechat.article.index")->with('articles',$articles)->with('filter',$filter);
    }

    /*审核*/
    public function verifyAuthor(Request $request)
    {
        $articleIds = $request->input('id');
        WechatMpInfo::whereIn('_id',$articleIds)->update(['status'=>1]);
        Artisan::queue('scraper:wechat:author');
        return $this->success(route('admin.scraper.wechat.author.index'),'审核成功,正在抓取文章数据,请稍候');
    }

    /*审核*/
    public function verifyArticle(Request $request)
    {
        $articleIds = $request->input('id');
        WechatWenzhangInfo::whereIn('_id',$articleIds)->update(['status'=>1]);
        foreach ($articleIds as $articleId) {
            $article = WechatWenzhangInfo::find($articleId);
            if ($article->topic_id > 0) continue;
            dispatch(new ArticleToSubmission($articleId));
        }
        return $this->success(route('admin.scraper.wechat.article.index'),'审核成功');
    }

    public function sync(Request $request){
        Artisan::queue('scraper:wechat:author');
        return $this->success(route('admin.scraper.wechat.author.index'),'正在抓取文章数据,请稍候');
    }


    public function createAuthor()
    {
        return view("admin.scraper.wechat.author.create");
    }

    public function editAuthor($id)
    {
        $author = WechatMpInfo::find($id);
        return view("admin.scraper.wechat.author.edit")->with('author',$author);
    }

    public function updateAuthor(Request $request) {
        $validateRules = [
            'id'      => 'required',
            'group_id'   => 'required|integer',
            'audit_status' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $author = WechatMpInfo::find($request->input('id'));
        $author->group_id = $request->input('group_id');
        $author->status = $request->input('audit_status');
        $author->save();
        return $this->success(route('admin.scraper.wechat.author.index'),'修改成功');
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

        $this->validateRules['wx_hao'] = 'required|max:255|unique:scraper_wechat_add_mp_list';

        $this->validate($request,$this->validateRules);

        $data = [
            'wx_hao'        => trim($request->input('wx_hao')),
            'name'  =>$request->input('wx_hao'),
        ];

        $news = WechatMpList::create($data);

        if($news){
            $message = '发布成功!请稍等片刻,正在为您抓取公众号信息 ';
            Artisan::queue('scraper:wechat:author');
            return $this->success(route('admin.scraper.wechat.author.index'),$message);
        }

        return  $this->error("发布失败，请稍后再试",route('admin.scraper.wechat.author.index'));

    }

    /**
     * 删除
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyAuthor(Request $request)
    {
        WechatMpInfo::whereIn('_id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.scraper.wechat.author.index'),'禁用成功');
    }

    public function destroyArticle(Request $request){
        WechatWenzhangInfo::whereIn('_id',$request->input('id'))->update(['status'=>0]);
        return $this->success(route('admin.scraper.wechat.article.index'),'禁用成功');
    }
}
