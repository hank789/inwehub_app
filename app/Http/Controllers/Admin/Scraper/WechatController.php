<?php

namespace App\Http\Controllers\Admin\Scraper;

use App\Http\Controllers\Admin\AdminController;
use App\Jobs\ArticleToSubmission;
use App\Logic\TagsLogic;
use App\Models\Groups\Group;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatMpList;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Tag;
use Illuminate\Http\Request;
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

        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->get()->toArray();

        $list = WechatMpList::all();
        $query = WechatMpInfo::query();

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', '%'.$filter['word'].'%');
        }

        if( isset($filter['wx_hao']) && $filter['wx_hao'] ){
            $query->where('wx_hao', $filter['wx_hao']);
        }

        if( isset($filter['user_id']) && $filter['user_id'] ){
            $query->where('user_id', $filter['user_id']);
        }

        if( isset($filter['group_id']) && $filter['group_id']>=0 ){
            $query->where('group_id', $filter['group_id']);
        } else {
            $filter['group_id'] = -1;
        }

        if( isset($filter['select_region']) && $filter['select_region']>=0 ){
            $query = $query->whereHas('tags',function($query) use ($filter) {
                $query->where('tag_id', $filter['select_region']);
            });
        } else {
            $filter['select_region'] = -1;
        }

        /*问题状态过滤*/
        if( isset($filter['status']) && $filter['status'] > -1 ){
            $query->where('status','=',$filter['status']);
        }

        $regions = TagsLogic::loadTags(6,'','id')['tags'];

        $authors = $query->orderBy('create_time','desc')->paginate(20);
        return view("admin.scraper.wechat.author.index")->with('authors',$authors)->with('filter',$filter)->with('pending',$list)->with('groups',$groups)->with('regions',$regions);
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
        return $this->success(route('admin.scraper.wechat.author.index'),'审核成功,若要马上抓取数据，点击"搜索"旁边的同步按钮');
    }

    /*审核*/
    public function verifyArticle(Request $request)
    {
        $articleIds = $request->input('id');
        WechatWenzhangInfo::whereIn('_id',$articleIds)->update(['status'=>2]);
        return $this->success(url()->previous(),'审核成功');
    }

    public function sync(Request $request){
        Artisan::queue('scraper:wechat:gzh:author');
        return $this->success(route('admin.scraper.wechat.author.index'),'正在抓取公众号数据,请稍候');
    }


    public function createAuthor()
    {
        return view("admin.scraper.wechat.author.create");
    }

    public function editAuthor($id)
    {
        $author = WechatMpInfo::find($id);
        $regions = TagsLogic::loadTags(6,'','id');
        $groups = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->get()->toArray();
        return view("admin.scraper.wechat.author.edit")->with('author',$author)->with('groups',$groups)->with('tags',$regions['tags']);
    }

    public function updateAuthor(Request $request) {
        $validateRules = [
            'id'      => 'required',
            'group_id'   => 'required|integer|min:0',
            'user_id'   => 'required|integer|min:1',
            'audit_status' => 'required|integer',
            'is_auto_publish' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $author = WechatMpInfo::find($request->input('id'));
        $author->group_id = $request->input('group_id');
        $author->user_id = $request->input('user_id',504);
        $author->status = $request->input('audit_status');
        $author->is_auto_publish = $request->input('is_auto_publish',0);
        $author->save();
        $tagString = $request->input('tagIds');
        if ($tagString != -1) {
            /*更新标签*/
            $oldTags = $author->tags->pluck('id')->toArray();
            if (!is_array($tagString)) {
                $tags = array_unique(explode(",",$tagString));
            } else {
                $tags = array_unique($tagString);
            }
            foreach ($tags as $tag) {
                if (!in_array($tag,$oldTags)) {
                    $author->tags()->attach($tag);
                }
            }
            foreach ($oldTags as $oldTag) {
                if (!in_array($oldTag,$tags)) {
                    $author->tags()->detach($oldTag);
                }
            }
        }
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
        $mpInfo = WechatMpInfo::where('wx_hao',trim($request->input('wx_hao')))->first();
        if ($mpInfo) {
            return  $this->error("此公众号已存在",route('admin.scraper.wechat.author.index'));
        }

        $news = WechatMpList::create($data);

        if($news){
            $message = '发布成功!请稍等片刻,正在为您抓取公众号信息 ';
            Artisan::queue('scraper:wechat:gzh:author');
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
        WechatWenzhangInfo::whereIn('_id',$request->input('id'))->update(['status'=>3]);
        return $this->success(url()->previous(),'禁用成功');
    }
}
