<?php

namespace App\Http\Controllers\Admin\Review;

use App\Events\Frontend\System\ExceptionNotify;
use App\Http\Controllers\Admin\AdminController;
use App\Jobs\UpdateProductInfoCache;
use App\Logic\TagsLogic;
use App\Models\Category;
use App\Models\ContentCollection;
use App\Models\ProductUserRel;
use App\Models\Scraper\WechatMpInfo;
use App\Models\Scraper\WechatMpList;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\RateLimiter;
use App\Services\Spiders\Wechat\MpSpider;
use App\Services\Spiders\Wechat\WechatSogouSpider;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use QL\QueryList;
use Illuminate\Support\Facades\Artisan;

class ProductController extends AdminController
{
    /*权限验证规则*/
    protected $validateRules = [
        'name' => 'required|max:128',
        'url' => 'sometimes|max:128',
        'summary' => 'sometimes',
        'description' => 'sometimes|max:65535',
    ];


    /**
     *标签管理
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter =  $request->all();
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->leftJoin('tags','tag_id','=','tags.id');

        $filter['category_id'] = $request->input('category_id',-1);

        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('name','like', $filter['word'].'%')->orderByRaw('case when name like "'.$filter['word'].'" then 0 else 2 end');
        }

        /*分类过滤*/
        if( $filter['category_id']> 0 ){
            $query->where('tag_category_rel.category_id','=',$filter['category_id']);
        }

        if( isset($filter['status']) && $filter['status'] >=0 ){
            $query->where('status',$filter['status']);
        }

        if( isset($filter['id']) && $filter['id'] ){
            $query->where('tag_id',$filter['id']);
        }

        if (isset($filter['onlyZh']) && $filter['onlyZh']) {
            $query->whereRaw('length(name)!=char_length(name)');
        }

        if (isset($filter['order_by']) && $filter['order_by']) {
            $orderBy = explode('|',$filter['order_by']);
            $query->orderBy('tag_category_rel.'.$orderBy[0],$orderBy[1]);
        } elseif (!(isset($filter['word']) && $filter['word'])){
            $query->orderBy('tag_category_rel.tag_id','desc');
        }

        $fields = ['tag_category_rel.id','tag_category_rel.tag_id','tag_category_rel.category_id','tag_category_rel.status','tag_category_rel.reviews','tags.name','tags.logo','tags.summary','tags.created_at'];
        $tags = $query->select($fields)->paginate(20);
        return view("admin.review.product.index")->with('tags',$tags)->with('filter',$filter);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.review.product.create');
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
        $this->validate($request,$this->validateRules);
        $data = $request->all();
        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $data['logo'] = $img_url;
        }
        $category_ids = $request->input('category_id');
        unset($data['category_id']);
        $data['category_id'] = $category_ids[0];
        $tag = Tag::where('name',$request->input('name'))->first();
        if (!$tag) {
            $tag = Tag::create($data);
        } else {
            unset($data['name']);
            $tag->update($data);
        }
        $keywords = $request->input('description');
        $website = $request->input('website');

        $tag->setDescription(['keywords'=>$keywords,'website'=>$website]);
        TagsLogic::cacheProductTags($tag);
        foreach ($category_ids as $category_id) {
            if ($category_id<=0) continue;
            $rel = TagCategoryRel::where('tag_id',$tag->id)->where('category_id',$category_id)->first();
            if (!$rel) {
                TagCategoryRel::create([
                    'tag_id' => $tag->id,
                    'category_id' => $category_id,
                    'type' => TagCategoryRel::TYPE_REVIEW,
                    'status' => $request->input('status',1)
                ]);
            }
        }
        $tag->updated_at = date('Y-m-d H:i:s');
        $tag->save();
        TagsLogic::delCache();
        TagsLogic::delRelatedProductsCache();
        return $this->success(route('admin.review.product.index'),'产品创建成功');
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
        $tag = TagCategoryRel::find($id);
        if(!$tag){
           abort(404);
        }
        //$categories = $tag->categories->pluck('id')->toArray();
        $initialPreview = $tag->tag->getIntroducePic();
        $initialPreviewConfig = [];
        foreach ($initialPreview as $key=>$img) {
            $initialPreviewConfig[] = [
                'caption' => '',
                'width' => '120px',
                'url' => route('admin.review.product.deleteIntroducePic',['id'=>$tag->tag_id]),
                'key' => $img['sort'],
                'extra' => ['url'=>$img['url']]
            ];
        }
        $ideas = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_EXPERT_IDEA)
            ->where('source_id',$tag->tag_id)
            ->whereIn('status',[0,1])
            ->orderBy('sort','desc')->get();
        $ideaList = [];
        for ($i=1;$i<=10;$i++) {
            $ideaList[] = [
                'id' => 0,
                'avatar' => '',
                'name' => '',
                'title' => '',
                'content' => '',
                'sort' => 10-$i
            ];
        }
        foreach ($ideas as $v=>$idea) {
            $ideaList[$v] = [
                'id' => $idea->id,
                'avatar' => $idea->content['avatar'],
                'name' => $idea->content['name'],
                'title' => $idea->content['title'],
                'content' => $idea->content['content'],
                'sort' => $idea->sort
            ];
        }
        $caseList = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_SHOW_CASE)
            ->where('source_id',$tag->tag_id)
            ->whereIn('status',[0,1])
            ->orderBy('sort','desc')->get();

        $gzhList = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('source_id',$tag->tag_id)->orderBy('id','desc')->get();
        $rel_tags = $tag->tag->getRelateProducts();
        $only_show_relate_products = $tag->tag->getOnlyShowRelateProducts();

        //产品维护人员
        $managers = ProductUserRel::where('tag_id',$tag->tag_id)->get();
        return view('admin.review.product.edit')->with('tag',$tag)
            ->with('initialPreview',json_encode(array_column($initialPreview,'url')))
            ->with('ideaList',$ideaList)
            ->with('caseList',$caseList)
            ->with('gzhList',$gzhList)
            ->with('rel_tags',$rel_tags)
            ->with('managers',$managers)
            ->with('only_show_relate_products',$only_show_relate_products)
            ->with('initialPreviewConfig',json_encode($initialPreviewConfig));
    }

    public function updateRelateProducts(Request $request, $tag_id) {
        $tag = Tag::find($tag_id);
        $rel_tags = $request->input('rel_tags');
        $isOnlyShow = $request->input('isOnlyShow',0);
        $tag->setDescription(['rel_tags'=>$rel_tags,'only_show_relate_products'=>$isOnlyShow]);
        $tag->save();
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        return response()->json(['message'=>'success']);
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
        $tagRel = TagCategoryRel::find($id);
        if(!$tagRel){
            return $this->error(route('admin.review.product.index'),'产品不存在，请核实');
        }
        $oldStatus = $tagRel->status;
        $newStatus = $request->input('status');
        $tag = Tag::find($tagRel->tag_id);
        $oldSummary = $tag->summary;
        $this->validateRules['name'] = 'required|max:128|unique:tags,name,'.$tag->id;
        $this->validate($request,$this->validateRules);
        $tag->name = $request->input('name');
        $tag->summary = $request->input('summary');
        $tag->is_pro = $request->input('is_pro',0);

        if($request->hasFile('logo')){
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $tag->logo = $img_url;
        }
        $cover_pic = $tag->getCoverPic();
        if($request->hasFile('cover_pic')){
            $file = $request->file('cover_pic');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
            $cover_pic = $img_url;
        }

        $keywords = $request->input('description');
        $advance_desc = $request->input('advance_desc');
        $website = $request->input('website');

        $tag->setDescription(['keywords'=>$keywords,'cover_pic'=>$cover_pic,'advance_desc'=>$advance_desc,'website'=>$website]);
        $tag->save();
        if($request->hasFile('logo') || $oldSummary != $tag->summary){
            $tag->clearMediaCollection('images_big');
            $tag->clearMediaCollection('images_small');
        }
        TagsLogic::cacheProductTags($tag);
        $category_ids = $request->input('category_id');
        $returnUrl = url()->previous();
        $delete = true;
        if ($category_ids) {
            foreach ($category_ids as $category_id) {
                if ($category_id<=0) continue;
                if ($category_id == $tagRel->category_id) $delete = false;

                $newRel = TagCategoryRel::firstOrCreate([
                    'tag_id' => $tag->id,
                    'category_id' => $category_id
                ],[
                    'tag_id' => $tag->id,
                    'category_id' => $category_id,
                    'type' => TagCategoryRel::TYPE_REVIEW,
                    'status' => $request->input('status',1)
                ]);
                if ($newRel->status != $request->input('status',1)) {
                    $newRel->status = $request->input('status',1);
                    $newRel->save();
                }
            }
            if ($delete) {
                TagCategoryRel::where('id',$id)->where('reviews',0)->delete();
                $returnUrl = route('admin.review.product.index');
            }
        }
        if ($newStatus == 1 && $oldStatus == 0) {
            $submissions = Submission::where('category_id',$tagRel->tag_id)->where('status',1)->get();
            $count = 0;
            $rates = 0;
            foreach ($submissions as $submission) {
                if (is_array($submission->data['category_ids']) && in_array($tagRel->category_id,$submission->data['category_ids'])) {
                    $count++;
                    $rates+=$submission->rate_star;
                }
            }
            $tagRel->calcRate();
            $info = Tag::getReviewInfo($tagRel->tag_id);
            Tag::where('id',$tagRel->tag_id)->update([
                'reviews' => $info['review_count']
            ]);
        }
        $tag->updated_at = date('Y-m-d H:i:s');
        $tag->save();
        $managers = $request->input('author_id_select');
        if ($managers) {
            ProductUserRel::where('tag_id',$tag->id)->delete();
            foreach ($managers as $mid) {
                $exist = ProductUserRel::where('user_id',$mid)->first();
                if (!$exist) {
                    ProductUserRel::create([
                        'user_id' => $mid,
                        'tag_id' => $tag->id
                    ]);
                }
            }
        } else {
            ProductUserRel::where('tag_id',$tag->id)->delete();
        }
        if ($newStatus != $oldStatus) {
            TagsLogic::delRelatedProductsCache();
        }
        TagsLogic::delCache();
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        return $this->success($returnUrl,'产品修改成功');
    }

    public function updateIntroducePic(Request $request, $id) {
        RateLimiter::instance()->lock_acquire('updateIntroducePic');
        $tag = Tag::find($id);
        $images = $tag->getIntroducePic();
        if ($images) {
            usort($images,function ($a,$b) {
                if ($a['sort'] == $b['sort']) {
                    return 0;
                }
                return ($a['sort'] < $b['sort']) ? -1 : 1;
            });
            $baseSort = $images[count($images)-1]['sort']+1;
        } else {
            $baseSort = 0;
        }

        $imgUrls = [];
        $initialPreviewConfig = [];
        $sort = $request->input('file_id',-1);
        if($request->hasFile('introduce_pic')){
            $files = $request->file('introduce_pic');
            foreach ($files as $key=>$file) {
                $extension = $file->getClientOriginalExtension();
                $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
                Storage::disk('oss')->put($filePath,File::get($file));
                $imgUrl = Storage::disk('oss')->url($filePath);
                $imgUrls[] = ['url'=>$imgUrl,'sort'=>$baseSort+($sort >= 0?$sort:$key)];
                $initialPreviewConfig[] = [
                    'caption' => '',
                    'width' => '120px',
                    'url' => route('admin.review.product.deleteIntroducePic',['id'=>$id]),
                    'key' => ($sort >= 0?$sort:$key),
                    'extra' => ['url'=>$imgUrl]
                ];
            }
        }
        $tag->setDescription(['introduce_pic'=>array_merge($images,$imgUrls)]);
        $tag->save();
        RateLimiter::instance()->lock_release('updateIntroducePic');
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        return response()->json([
            'initialPreview' => array_column($imgUrls,'url'),
            'initialPreviewConfig' => $initialPreviewConfig
        ]);
    }

    public function deleteIntroducePic(Request $request, $id) {
        RateLimiter::instance()->lock_acquire('deleteIntroducePic');
        $tag = Tag::find($id);
        $images = $tag->getIntroducePic();
        $url = $request->input('url',-1);
        foreach ($images as $i=>$image) {
            if ($image['url'] == $url) {
                unset($images[$i]);
            }
        }
        $tag->setDescription(['introduce_pic'=>$images]);
        $tag->save();
        RateLimiter::instance()->lock_release('deleteIntroducePic');
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        return response()->json(['message'=>'success']);
    }

    public function sortIntroducePic(Request $request, $id) {
        $tag = Tag::find($id);
        $newList = $request->input('newList',[]);
        $urls = [];
        foreach ($newList as $key=>$item) {
            $urls[] = [
                'sort' => $key,
                'url' => $item['extra']['url']
            ];
        }
        $tag->setDescription(['introduce_pic'=>$urls]);
        $tag->save();
        $this->dispatch(new UpdateProductInfoCache($tag->id));
        return response()->json(['message'=>'success']);
    }

    /*修改分类*/
    public function changeCategories(Request $request){
        $ids = $request->input('ids','');
        $categoryIds = explode(',',$request->input('category_id',0));
        $albumIds = explode(',',$request->input('album_id',0));
        $categoryIds = array_merge($categoryIds,$albumIds);
        if($ids){
            $idArray = explode(",",$ids);
            foreach ($idArray as $id) {
                $tag = Tag::find($id);
                $cids = $tag->categories->pluck('id')->toArray();
                foreach ($categoryIds as $categoryId) {
                    if ($categoryId<=0) continue;
                    foreach ($cids as $key=>$cid) {
                        if ($categoryId == $cid) {
                            unset($cids[$key]);
                        }
                    }
                    TagCategoryRel::firstOrCreate([
                        'tag_id' => $id,
                        'category_id' => $categoryId
                    ],
                        [
                            'tag_id' => $id,
                            'category_id' => $categoryId,
                            'type' => TagCategoryRel::TYPE_REVIEW
                        ]);
                }
                if (count($cids)) {
                    TagCategoryRel::where('tag_id',$id)->whereIn('category_id',$cids)->delete();
                }
                $tag->updated_at = date('Y-m-d H:i:s');
                $tag->save();
                $this->dispatch(new UpdateProductInfoCache($tag->id));
            }
        }
        return response('success');
    }

    //审核
    public function setVeriy(Request $request) {
        $articleId = $request->input('id');
        $article = TagCategoryRel::find($articleId);
        if ($article->status) {
            $article->status = 0;
        } else {
            $article->status = 1;
        }
        $article->save();
        if ($article->status) {
            return response('failed');
        }
        return response('success');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $tagIds = $request->input('ids');
        TagCategoryRel::where('id',$tagIds)->delete();
        return response('success');
    }

    public function deleteIdea(Request $request) {
        $id = $request->input('id');
        $model = ContentCollection::find($id);
        $model->delete();
        $this->dispatch(new UpdateProductInfoCache($model->source_id));
        return response()->json(['id'=>$id]);
    }

    public function saveIdea(Request $request,$tag_id) {
        $validateRules = [
            'name' => 'required|max:128',
            'title' => 'required|max:128',
            'content' => 'required',
            'sort' => 'required',
            'file' => 'required|file',
            'id' => 'required'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();
        $img_url = '';
        if($request->hasFile('file')){
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        }
        $id = $data['id'];
        if ($id) {
            $model = ContentCollection::find($id);
            $model->update([
                'sort' => $data['sort'],
                'content' => [
                    'avatar' => $img_url,
                    'name' => $data['name'],
                    'title' => $data['title'],
                    'content' => $data['content']
                ]
            ]);
        } else {
            $model = ContentCollection::create([
                'content_type' => ContentCollection::CONTENT_TYPE_TAG_EXPERT_IDEA,
                'sort' => $data['sort'],
                'source_id' => $tag_id,
                'content' => [
                    'avatar' => $img_url,
                    'name' => $data['name'],
                    'title' => $data['title'],
                    'content' => $data['content']
                ]
            ]);
        }
        $this->dispatch(new UpdateProductInfoCache($tag_id));
        return response()->json(['id'=>$model->id]);
    }

    public function deleteCase(Request $request) {
        $id = $request->input('id');
        $model = ContentCollection::find($id);
        $model->delete();
        $this->dispatch(new UpdateProductInfoCache($model->source_id));
        return response()->json(['id'=>$id]);
    }

    public function editCase(Request $request,$id) {
        $case = ContentCollection::find($id);
        $tag = Tag::find($case->source_id);
        return view('admin.review.product.editCase')->with('tag',$tag)->with('case',$case);
    }

    public function addCase(Request $request,$tag_id) {
        $tag = Tag::find($tag_id);
        if(!$tag){
            abort(404);
        }
        return view('admin.review.product.addCase')->with('tag',$tag);
    }

    public function storeCase(Request $request,$tag_id) {
        $tag = Tag::find($tag_id);
        if(!$tag){
            abort(404);
        }
        $validateRules = [
            'title' => 'required|max:128',
            'desc' => 'required',
            'sort' => 'required',
            'cover_pic' => 'required|file',
            'type' => 'required',
            'link_url' => 'required_if:type,link,video',
            'file' => 'required_if:type,pdf,image'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();
        if ($data['type'] == 'link') {
            $link_url = parse_url($data['link_url']);
            if ($link_url['host'] != 'mp.weixin.qq.com') {
                return $this->error(url()->previous(),'暂时不支持非微信公众号的链接地址');
            }
        }
        if ($data['type'] == 'pdf' || $data['type'] == 'image') {
            if($request->hasFile('file')){
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
                Storage::disk('oss')->put($filePath,File::get($file));
                $data['link_url'] = Storage::disk('oss')->url($filePath);
            } else {
                return $this->error(url()->previous(),'未上传案例文件');
            }
        }
        $img_url = '';
        if($request->hasFile('cover_pic')){
            $file = $request->file('cover_pic');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $img_url = Storage::disk('oss')->url($filePath);
        }

        if ($data['type'] == 'link') {
            $linkInfo = getWechatUrlInfo($data['link_url'],false,true);
            $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
            if (!$mpInfo) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $linkInfo['author'],
                    'wx_hao' => $linkInfo['wxHao'],
                    'company' => $linkInfo['author'],
                    'description' => '',
                    'logo_url' => '',
                    'qr_url' => '',
                    'wz_url' => '',
                    'last_qunfa_id' => 0,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
            $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
            $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
            if ($aid) {
                $article = WechatWenzhangInfo::find($aid);
                $article->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
                $article->save();
            } else {
                $article = WechatWenzhangInfo::create([
                    'title' => $linkInfo['title'],
                    'source_url' => '',
                    'content_url' => $data['link_url'],
                    'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                    'description' => '',
                    'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                    'mp_id' => $mpInfo->_id,
                    'author' => $linkInfo['author'],
                    'msg_index' => 0,
                    'copyright_stat' => 0,
                    'qunfa_id' => 0,
                    'body' => $linkInfo['body'],
                    'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                    'like_count' => 0,
                    'read_count' => 0,
                    'status' => 2,
                    'comment_count' => 0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
            }
            $data['link_url'] = config('app.url').'/articleInfo/'.$article->_id.'?inwehub_user_device=weapp_dianping&source=product_'.$tag->id;
        }
        $model = ContentCollection::create([
            'content_type' => ContentCollection::CONTENT_TYPE_TAG_SHOW_CASE,
            'sort' => $data['sort'],
            'source_id' => $tag_id,
            'status' => $data['status'],
            'content' => [
                'cover_pic' => $img_url,
                'title' => $data['title'],
                'desc' => $data['desc'],
                'link_url' => $data['link_url'],
                'type' => $data['type']
            ]
        ]);
        $this->dispatch(new UpdateProductInfoCache($tag_id));
        return $this->success(url()->previous(),'案例添加成功');
    }

    public function updateCase(Request $request,$id) {
        $validateRules = [
            'title' => 'required|max:128',
            'desc' => 'required',
            'sort' => 'required',
            'type' => 'required',
            'link_url' => 'required',
        ];
        $this->validate($request,$validateRules);
        $case = ContentCollection::find($id);
        $data = $request->all();
        $content = $case->content;
        if ($data['type'] == 'link') {
            $link_url = parse_url($data['link_url']);
            if ($link_url['host'] != 'mp.weixin.qq.com' && !in_array($link_url['host'],['api.inwehub.com','api.ywhub.com'])) {
                return $this->error(url()->previous(),'暂时不支持非微信公众号的链接地址');
            }
        }
        if ($data['type'] == 'pdf' || $data['type'] == 'image') {
            if($request->hasFile('file')){
                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
                Storage::disk('oss')->put($filePath,File::get($file));
                $content['link_url'] = Storage::disk('oss')->url($filePath);
            }
        }
        if($request->hasFile('cover_pic')){
            $file = $request->file('cover_pic');
            $extension = $file->getClientOriginalExtension();
            $filePath = 'tags/'.gmdate("Y")."/".gmdate("m")."/".uniqid(str_random(8)).'.'.$extension;
            Storage::disk('oss')->put($filePath,File::get($file));
            $content['cover_pic'] = Storage::disk('oss')->url($filePath);
        }

        if ($data['type'] == 'video' && $data['link_url'] != $case->content['link_url']) {
            $content['link_url'] = $data['link_url'];
        }

        if ($data['type'] == 'link' && $data['link_url'] != $case->content['link_url']) {
            $linkInfo = getWechatUrlInfo($data['link_url'],false,true);
            $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
            if (!$mpInfo) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $linkInfo['author'],
                    'wx_hao' => $linkInfo['wxHao'],
                    'company' => $linkInfo['author'],
                    'description' => '',
                    'logo_url' => '',
                    'qr_url' => '',
                    'wz_url' => '',
                    'last_qunfa_id' => 0,
                    'status' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
            $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
            $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
            if ($aid) {
                $article = WechatWenzhangInfo::find($aid);
                $article->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
                $article->save();
            } else {
                $article = WechatWenzhangInfo::create([
                    'title' => $linkInfo['title'],
                    'source_url' => '',
                    'content_url' => $data['link_url'],
                    'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                    'description' => '',
                    'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                    'mp_id' => $mpInfo->_id,
                    'author' => $linkInfo['author'],
                    'body' => $linkInfo['body'],
                    'msg_index' => 0,
                    'copyright_stat' => 0,
                    'qunfa_id' => 0,
                    'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                    'like_count' => 0,
                    'read_count' => 0,
                    'status' => 2,
                    'comment_count' => 0
                ]);
                RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
            }
            $content['link_url'] = config('app.url').'/articleInfo/'.$article->_id.'?inwehub_user_device=weapp_dianping&source=product_'.$case->source_id;;
        }

        $content['title'] = $data['title'];
        $content['type'] = $data['type'];
        $content['desc'] = $data['desc'];
        $case->sort = $data['sort'];
        $case->status = $data['status'];
        $case->content = $content;
        $case->save();
        $this->dispatch(new UpdateProductInfoCache($case->source_id));

        return $this->success(url()->previous(),'案例修改成功');
    }

    public function newsList(Request $request,$tag_id) {
        $filter =  $request->all();
        $tag = Tag::find($tag_id);

        $query = WechatWenzhangInfo::where('source_type',1)
            ->where('type',WechatWenzhangInfo::TYPE_TAG_NEWS)
            ->whereHas('tags',function($query) use ($tag_id) {
            $query->where('tag_id', $tag_id);
        });

        /*公众号id过滤*/
        if( isset($filter['mp_id']) && $filter['mp_id'] > -1 ){
            $query->where('mp_id','=',$filter['mp_id']);
        }
        /*问题标题过滤*/
        if( isset($filter['word']) && $filter['word'] ){
            $query->where('title','like', '%'.$filter['word'].'%');
        }

        $newsList = $query->orderBy('_id','desc')->simplePaginate(20);
        return view('admin.review.product.newsList')->with('articles',$newsList)->with('filter',$filter)->with('tag',$tag);
    }

    public function addNews(Request $request,$tag_id) {
        $tag = Tag::find($tag_id);
        return view('admin.review.product.addNews')->with('tag',$tag);
    }

    public function addGzh(Request $request,$tag_id) {
        $tag = Tag::find($tag_id);
        return view('admin.review.product.addGzh')->with('tag',$tag);
    }

    public function storeGzh(Request $request,$tag_id) {
        $wx_hao = trim($request->input('wx_hao'));
        $mpInfo = WechatMpInfo::where('wx_hao',$wx_hao)->first();
        if ($mpInfo) {
            $exist = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
                ->where('source_id',$tag_id)
                ->where('sort',$mpInfo->_id)
                ->first();
            if (!$exist) {
                ContentCollection::create([
                    'content_type' => ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH,
                    'sort' => $mpInfo->_id,
                    'source_id' => $tag_id,
                    'status' => 1,
                    'content' => [
                        'wx_hao' => $wx_hao,
                        'mp_id' => $mpInfo->_id
                    ]
                ]);
            }
            if ($mpInfo->status != 1) {
                $mpInfo->status = 1;
                $mpInfo->save();
            }
            return $this->success($request->input('url_previous'),'微信公众号添加成功');
        }
        $spider = new MpSpider();
        $data = $spider->getGzhInfo($wx_hao);
        if ($data) {
            $info = WechatMpInfo::where('wx_hao',$wx_hao)->first();
            if (!$info) {
                $mpInfo = WechatMpInfo::create([
                    'name' => $data['name'],
                    'wx_hao' => $data['wechatid'],
                    'company' => $data['company'],
                    'description' => $data['description'],
                    'logo_url' => $data['img'],
                    'qr_url' => $data['qrcode'],
                    'wz_url' => $data['url'],
                    'last_qunfa_id' => $data['last_qunfa_id'],
                    'is_auto_publish' => 0,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            $spider2 = new WechatSogouSpider();
            $data = $spider2->getGzhInfo($wx_hao);
            if ($data['name']) {
                $info = WechatMpInfo::where('wx_hao',$wx_hao)->first();
                if (!$info) {
                    $mpInfo = WechatMpInfo::create([
                        'name' => $data['name'],
                        'wx_hao' => $data['wechatid'],
                        'company' => $data['company'],
                        'description' => $data['description'],
                        'logo_url' => $data['img'],
                        'qr_url' => $data['qrcode'],
                        'wz_url' => $data['url'],
                        'is_auto_publish' => 0,
                        'status' => 1,
                        'last_qunfa_id' => $data['last_qunfa_id'],
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            } else {
                event(new ExceptionNotify('产品抓取微信公众号失败：'.$wx_hao));
            }
        }
        if ($mpInfo) {
            $exist = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
                ->where('source_id',$tag_id)
                ->where('sort',$mpInfo->_id)
                ->first();
            if (!$exist) {
                ContentCollection::create([
                    'content_type' => ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH,
                    'sort' => $mpInfo->_id,
                    'source_id' => $tag_id,
                    'status' => 1,
                    'content' => [
                        'wx_hao' => $wx_hao,
                        'mp_id' => $mpInfo->_id
                    ]
                ]);
            }
        } else {
            return  $this->error($request->input('url_previous'),"抓取失败，请稍后再试");
        }

        return $this->success($request->input('url_previous'),'微信公众号添加成功');
    }

    public function deleteGzh(Request $request) {
        $id = $request->input('id');
        ContentCollection::destroy($id);
        return response()->json(['id'=>$id]);
    }

    public function storeNews(Request $request,$tag_id) {
        $validateRules = [
            'link_url' => 'required|url'
        ];
        $this->validate($request,$validateRules);
        $link_url = $request->input('link_url');
        $parse_url = parse_url($link_url);
        if ($parse_url['host'] != 'mp.weixin.qq.com') {
            return $this->error(url()->previous(),'暂时不支持非微信公众号的链接地址');
        }
        $linkInfo = getWechatUrlInfo($link_url,false,true);
        $mpInfo = WechatMpInfo::where('wx_hao',$linkInfo['wxHao'])->first();
        if (!$mpInfo) {
            $mpInfo = WechatMpInfo::create([
                'name' => $linkInfo['author'],
                'wx_hao' => $linkInfo['wxHao'],
                'company' => $linkInfo['author'],
                'description' => '',
                'logo_url' => '',
                'qr_url' => '',
                'wz_url' => '',
                'last_qunfa_id' => 0,
                'status' => 0,
                'create_time' => date('Y-m-d H:i:s')
            ]);
        }
        $article_uuid = base64_encode($mpInfo->_id.$linkInfo['title'].date('Y-m-d',$linkInfo['date']));
        $aid = RateLimiter::instance()->hGet('wechat_article',$article_uuid);
        if ($aid) {
            $article = WechatWenzhangInfo::find($aid);
            $article->type = WechatWenzhangInfo::TYPE_TAG_NEWS;
            $article->save();
        } else {
            $article = WechatWenzhangInfo::create([
                'title' => $linkInfo['title'],
                'source_url' => '',
                'content_url' => $link_url,
                'cover_url'   => saveImgToCdn($linkInfo['cover_img'],'submissions'),
                'description' => '',
                'date_time'   => date('Y-m-d H:i:s',$linkInfo['date']),
                'mp_id' => $mpInfo->_id,
                'author' => $linkInfo['author'],
                'msg_index' => 0,
                'body' => $linkInfo['body'],
                'copyright_stat' => 0,
                'qunfa_id' => 0,
                'type' => WechatWenzhangInfo::TYPE_TAG_NEWS,
                'like_count' => 0,
                'status' => 2,
                'read_count' => 0,
                'comment_count' => 0
            ]);
            RateLimiter::instance()->hSet('wechat_article',$article_uuid,$article->_id);
        }
        Tag::multiAddByIds([$tag_id],$article);
        $this->dispatch(new UpdateProductInfoCache($tag_id));
        return $this->success($request->input('url_previous'),'资讯添加成功');
    }

    public function deleteNews(Request $request) {
        $id = $request->input('id');
        WechatWenzhangInfo::destroy($id);
        return response()->json(['id'=>$id]);
    }

    public function hotAlbums(Request $request) {
        $list = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_HOT_ALBUM)->orderBy('sort','asc')->get();
        $ideaList = [];
        for ($i=1;$i<=5;$i++) {
            $ideaList[] = [
                'id' => 0,
                'category_id' => 0,
                'desc' => '',
                'sort' => $i
            ];
        }
        foreach ($list as $v=>$idea) {
            $ideaList[$v] = [
                'id' => $idea->id,
                'category_id' => $idea->source_id,
                'desc' => $idea->content['desc'],
                'sort' => $idea->sort
            ];
        }
        return view('admin.review.product.hotAlbums')->with('list',$ideaList);
    }

    public function saveHotAlbum(Request $request) {
        $validateRules = [
            'id' => 'required',
            'category_id' => 'required|min:1',
            'desc' => 'required',
            'sort' => 'required'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();
        $id = $data['id'];
        if ($id) {
            $model = ContentCollection::find($id);
            $model->update([
                'sort' => $data['sort'],
                'source_id' => $data['category_id'],
                'content' => [
                    'desc' => $data['desc']
                ]
            ]);
        } else {
            $model = ContentCollection::create([
                'content_type' => ContentCollection::CONTENT_TYPE_HOT_ALBUM,
                'sort' => $data['sort'],
                'source_id' => $data['category_id'],
                'content' => [
                    'desc' => $data['desc']
                ]
            ]);
        }
        return response()->json(['id'=>$model->id]);
    }

    public function deleteHotAlbum(Request $request) {
        $id = $request->input('id');
        $model = ContentCollection::find($id);
        $model->delete();
        return response()->json(['id'=>$id]);
    }


}
