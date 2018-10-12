<?php

namespace App\Http\Controllers\Blog;

use App\Jobs\CloseActivity;
use App\Models\Article;
use App\Models\Collection;
use App\Models\Question;
use App\Models\Tag;
use App\Models\UserData;
use App\Models\UserTag;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{

    /*问题创建校验*/
    protected $validateRules = [
        'title' => 'required|min:5|max:255',
        'content' => 'required|min:50|max:16777215',
        'summary' => 'sometimes|max:255',
        'tags' => 'sometimes|max:128',
        'category_id' => 'required|numeric'
    ];

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("theme::article.create");
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

        /*如果开启验证码则需要输入验证码*/
        if( Setting()->get('code_create_article') ){
            $this->validateRules['captcha'] = 'required|captcha';
        }

        $this->validate($request,$this->validateRules);

        $deadline = $request->input('deadline');

        $data = [
            'user_id'      => $loginUser->id,
            'category_id'      => intval($request->input('category_id',0)),
            'title'        => trim($request->input('title')),
            'content'  => clean($request->input('content')),
            'summary'  => $request->input('summary'),
            'status'       => 0,
            'deadline' => $deadline
        ];

        if($request->hasFile('logo')){
            $validateRules = [
                'logo' => 'required|image|max:'.config('inwehub.upload.image.max_size'),
            ];
            $this->validate($request,$validateRules);
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'articles/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $data['logo'] = $img_url;
        }


        $article = Article::create($data);

        /*判断问题是否添加成功*/
        if($article){

            $recommend_home_sort = $request->input('recommend_home_sort');
            if ($recommend_home_sort) {
                Redis::connection()->hset('recommend_home_ac', $recommend_home_sort, $article->id);
                $recommend_home_img = $request->input('recommend_home_img');
                Redis::connection()->hset('recommend_home_ac_img', $recommend_home_sort, $recommend_home_img);
            }

            if($article->status === 1 ){
                $message = '活动发布成功! ';
            }else{
                $message = '活动发布成功！为了确保活动的质量，我们会对您发布的活动进行审核。请耐心等待......';
            }

            return $this->success(route('blog.article.detail',['id'=>$article->id]),$message);


        }

        return  $this->error("活动发布失败，请稍后再试",route('website.index'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        $article = Article::findOrFail($id);

        /*问题查看数+1*/
        $article->increment('views');

        /*相关文章*/
        $relatedArticles = Article::correlations($article->tags()->pluck('tag_id'));

        //收藏人
        $collectors = Collection::where('source_id',$id)->where('source_type',get_class($article))->get();

        return view("theme::article.show")
            ->with('article',$article)
            ->with('collectors',$collectors)
            ->with('relatedArticles',$relatedArticles);
    }

    /**
     * 显示文字编辑页面
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,Request $request)
    {
        $article = Article::find($id);

        if(!$article){
            abort(404);
        }

        if($article->user_id !== $request->user()->id && !$request->user()->hasPermission('admin.index.index')){
            abort(403);
        }

        /*编辑问题时效控制*/
        if( !$request->user()->hasPermission('admin.index.index') && Setting()->get('edit_article_timeout') ){
            if( $article->created_at->diffInMinutes() > Setting()->get('edit_article_timeout') ){
                return $this->showErrorMsg(route('website.index'),'你已超过文章可编辑的最大时长，不能进行编辑了。如有疑问请联系管理员!');
            }
        }
        $recommend_home_ac = Redis::connection()->hgetall('recommend_home_ac');
        $recommend_home_imgs = Redis::connection()->hgetall('recommend_home_ac_img');
        $recommend_home_sort = array_search($article->id,$recommend_home_ac)??'';
        $recommend_home_img = $recommend_home_imgs[$recommend_home_sort]??'';

        return view("theme::article.edit")->with(compact('article','recommend_home_img','recommend_home_sort'));

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
        $article = Article::find($article_id);
        if(!$article){
            abort(404);
        }

        if($article->user_id !== $request->user()->id && !$request->user()->hasPermission('admin.index.index')){
            abort(403);
        }

        $request->flash();

        /*如果开启验证码则需要输入验证码*/
        if( Setting()->get('code_create_article') ){
            $this->validateRules['captcha'] = 'required|captcha';
        }




        $this->validate($request,$this->validateRules);

        $deadline = $request->input('deadline');
        if ($deadline && time() >= strtotime($deadline)) {
            $article->status = Article::ARTICLE_STATUS_CLOSED;
        } elseif ($deadline && $article->status == Article::ARTICLE_STATUS_ONLINE) {
            $this->dispatch((new CloseActivity($article_id))->delay(Carbon::createFromTimestamp(strtotime($deadline))));
        } elseif ($deadline && $article->status == Article::ARTICLE_STATUS_CLOSED && time()<strtotime($deadline)) {
            $article->status = Article::ARTICLE_STATUS_ONLINE;
            $this->dispatch((new CloseActivity($article_id))->delay(Carbon::createFromTimestamp(strtotime($deadline))));
        }
        $recommend_home_sort = $request->input('recommend_home_sort');
        if ($recommend_home_sort) {
            Redis::connection()->hset('recommend_home_ac', $recommend_home_sort, $article_id);
            $recommend_home_img = $request->input('recommend_home_img');
            Redis::connection()->hset('recommend_home_ac_img', $recommend_home_sort, $recommend_home_img);
        }

        $article->title = trim($request->input('title'));
        $article->content = clean($request->input('content'));
        $article->summary = $request->input('summary');
        $article->category_id = $request->input('category_id',0);
        $article->deadline = $deadline;



        if($request->hasFile('logo')){
            $validateRules = [
                'logo' => 'required|image|max:'.config('inwehub.upload.image.max_size'),
            ];
            $this->validate($request,$validateRules);
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'articles/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $article->logo = $img_url;
        }


        $article->save();


        return $this->success(route('blog.article.detail',['id'=>$article->id]),"文章编辑成功");

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
