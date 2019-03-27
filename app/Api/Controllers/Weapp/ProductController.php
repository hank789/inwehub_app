<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\UpdateProductInfoCache;
use App\Jobs\UploadFile;
use App\Jobs\WeappActivity;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Company\CompanyData;
use App\Models\ContentCollection;
use App\Models\Doing;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Models\User;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use App\Traits\SubmitSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: hank.huiwang@gmail.com
 */

class ProductController extends Controller {
    use SubmitSubmission;
    //产品服务详情
    public function info(Request $request, JWTAuth $JWTAuth) {
        $validateRules = [
            'tag_name' => 'required'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        $tag_name = $request->input('tag_name');
        if (is_numeric($tag_name)) {
            $tag = Tag::find($tag_name);
            if (!$tag) {
                $tag = Tag::getTagByName($tag_name);
            }
        } else {
            $tag = Tag::getTagByName($tag_name);
        }
        if (!$tag) {
            throw new ApiException(ApiException::PRODUCT_TAG_NOT_EXIST);
        }
        $data = $tag->getProductCacheInfo();
        if (!$data) {
            $data = (new UpdateProductInfoCache($tag->id))->handle();
        }

        event(new SystemNotify('小程序用户'.$oauth->user_id.'['.$oauth->nickname.']查看产品详情:'.$tag->name));
        return self::createJsonData(true,$data);
    }

    //产品点评列表
    public function reviewList(Request $request, JWTAuth $JWTAuth) {
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));

        if (is_numeric($tag_name)) {
            $tag = Tag::find($tag_name);
            if (!$tag) {
                $tag = Tag::getTagByName($tag_name);
            }
        } else {
            $tag = Tag::getTagByName($tag_name);
        }
        if (!$tag) {
            throw new ApiException(ApiException::PRODUCT_TAG_NOT_EXIST);
        }
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $query = Submission::where('status',1)->where('category_id',$tag->id)->where('type','review');
        $submissions = $query->orderBy('is_recommend','desc')->orderBy('id','desc')->paginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $list[] = $submission->formatListItem($user, false);
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    public function newsList(Request $request, JWTAuth $JWTAuth) {
        $validateRules = [
            'tag_id' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_id = $request->input('tag_id');
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));

        $tag = Tag::find($tag_id);
        if (!$tag) {
            throw new ApiException(ApiException::PRODUCT_TAG_NOT_EXIST);
        }
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $news = WechatWenzhangInfo::where('source_type',1)
            ->where('type',WechatWenzhangInfo::TYPE_TAG_NEWS)
            ->whereHas('tags',function($query) use ($tag) {
                $query->where('tag_id', $tag->id);
            })
            ->orderBy('date_time','desc')->paginate($perPage);

        $return = $news->toArray();
        $list = [];
        foreach ($news as $new) {
            $list[] = [
                'id' => $new->_id,
                'title' => strip_tags($new->title),
                'type' => 'link',
                'date' => date('Y年m月d日',strtotime($new->date_time)),
                'author' => domain($new->content_url),
                'cover_pic' => $new->cover_url,
                'link_url' => config('app.url').'/articleInfo/'.$new->_id.'?inwehub_user_device=weapp_dianping&source=product_'.$tag_id
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    public function myReviewList(Request $request, JWTAuth $JWTAuth) {
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            return self::createJsonData(true,Submission::where('user_id',-1)->paginate(Config::get('inwehub.api_data_page_size'))->toArray());
        }
        if (empty($user->mobile)) {
            return self::createJsonData(true,Submission::where('user_id',-1)->paginate(Config::get('inwehub.api_data_page_size'))->toArray());
        }
        $submissions = Submission::where('user_id',$user->id)->where('type','review')->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $list[] = $submission->formatListItem($user, false);
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    public function reviewInfo(Request $request, JWTAuth $JWTAuth) {
        $this->validate($request, [
            'slug' => 'required',
        ]);

        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = $oauth->nickname;
        }
        $submission = Submission::where('slug',$request->slug)->first();
        if (!$submission) {
            $submission = Submission::find($request->slug);
            if (!$submission) {
                throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
            }
        }
        if ($submission->type != 'review') {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        $actionName = Doing::ACTION_VIEW_DIANPING_REVIEW_INFO;
        $actionUrl = config('app.mobile_url').'#/dianping/comment/'.$submission->slug;
        $return = $this->formatSubmissionInfo($request,$submission,$user);
        $comment = Comment::where('source_id',$submission->id)->where('source_type',get_class($submission))
            ->where('comment_type',Comment::COMMENT_TYPE_OFFICIAL)->where('status',1)->first();
        $return['official_reply'] = '';
        if ($comment) {
            $return['official_reply'] = [
                'author' => '官方回复',
                'content'=>$comment->content,
                'created_at' => $comment->created_at->diffForHumans()
            ];
        }
        $this->logUserViewTags($user->id,$submission->tags()->get());
        $this->doing($user,$actionName,get_class($submission),$submission->id,$submission->type == 'link'?$submission->data['title']:$submission->title,
            '',0,0,'',$actionUrl);
        return self::createJsonData(true,$return);
    }

    public function reviewCommentList(Request $request, JWTAuth $JWTAuth) {
        $this->validate($request, [
            'submission_slug' => 'required'
        ]);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $orderBy = $request->input('order_by',1);

        $submission = Submission::where('slug',$request->submission_slug)->first();
        if (!$submission) {
            $submission = Submission::find($request->submission_slug);
            if (!$submission) {
                throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
            }
        }
        if ($submission->type != 'review') {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }

        $query = $submission->comments()
            ->where('parent_id', 0);
        if ($orderBy == 1) {
            $query = $query->orderBy('created_at', 'desc');
        } else {
            $query = $query->orderBy('supports', 'desc')->orderBy('created_at', 'desc');
        }
        $comments = $query->simplePaginate($request->input('perPage',20));
        $return = $comments->toArray();
        $return['total'] = $submission->comments_number;
        foreach ($return['data'] as &$item) {
            $this->checkCommentIsSupported($user->id, $item);
        }

        return self::createJsonData(true,$return);
    }

    public function storeReview(Request $request, JWTAuth $JWTAuth) {
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEIXIN_NEED_REGISTER);
        }
        if (empty($user->mobile)) {
            throw new ApiException(ApiException::USER_NEED_VALID_PHONE);
        }
        return $this->storeSubmission($request,$user);
    }

    public function addReviewImage(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id' => 'required|integer',
            'image_file'=> 'required|image'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        $data = $request->all();
        $submission = Submission::find($data['id']);
        if ($submission->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $data = $submission->data;
        $image_file = 'image_file';
        if($request->hasFile($image_file)){
            $file_0 = $request->file($image_file);
            $extension = strtolower($file_0->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $file_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                dispatch((new UploadFile($file_name,base64_encode(File::get($file_0)))));
                //Storage::disk('oss')->put($file_name,File::get($file_0));
                $img_url = Storage::disk('oss')->url($file_name);
                $data['img'][] = $img_url;
            }
        }
        $submission->data = $data;
        $submission->save();
        return self::createJsonData(true,['id'=>$submission->id]);
    }

    public function getProductShareImage(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer',
            'type' => 'required|in:1,2'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        $type = $request->input('type',1);
        if ($type == 1) {
            //分享到朋友圈的长图
            $collection = 'images_big';
            $showUrl = 'getProductShareLongInfo';
        } else {
            //分享到公众号的短图
            $collection = 'images_small';
            $showUrl = 'getProductShareShortInfo';
        }
        $tag = Tag::find($request->input('id'));

        if($tag->getMedia($collection)->where('name',$oauth->id)->isEmpty()){
            $snappy = App::make('snappy.image');
            $snappy->setOption('width',1125);
            $image = $snappy->getOutput(config('app.url').'/weapp/'.$showUrl.'/'.$tag->id.'?oauth_id='.$oauth->id);
            $tag->addMediaFromBase64(base64_encode($image))->setName($oauth->id)->toMediaCollection($collection);
        }
        $tag = Tag::find($request->input('id'));
        $url = $tag->getMedia($collection)->last()->getUrl();
        return self::createJsonData(true,['url'=>$url]);
    }

    public function getAlbumShareImage(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'id'   => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        //分享到朋友圈的长图
        $collection = 'images_big';
        $showUrl = 'getAlbumShareLongInfo';

        $category = Category::findOrFail($request->input('id'));
        $url = RateLimiter::instance()->getValue('album_share_image',$category->id);
        if ($url && config('app.env') == 'production') {
            return self::createJsonData(true,['url'=>$url]);
        }

        if(empty($url) || config('app.env') != 'production'){
            $snappy = App::make('snappy.image');
            $snappy->setOption('width',1125);
            $image = $snappy->getOutput(config('app.url').'/weapp/'.$showUrl.'/'.$category->id);
            $category->addMediaFromBase64(base64_encode($image))->toMediaCollection($collection);
        }
        $category = Category::find($request->input('id'));
        $url = $category->getMedia($collection)->last()->getUrl();
        RateLimiter::instance()->setVale('album_share_image',$category->id,$url,60*60*12);
        return self::createJsonData(true,['url'=>$url]);
    }

    public function getReviewShareImage(Request $request){
        $validateRules = [
            'id'   => 'required|integer',
            'type' => 'required|in:1,2'
        ];
        $this->validate($request,$validateRules);
        $type = $request->input('type',1);
        if ($type == 1) {
            //分享到朋友圈的长图
            $collection = 'weapp_images_big';
            $showUrl = 'getReviewShareLongInfo';
        } else {
            //分享到公众号的短图
            $collection = 'weapp_images_small';
            $showUrl = 'getReviewShareShortInfo';
        }
        $submission = Submission::findOrFail($request->input('id'));
        if(!isset($submission->data[$collection]) || config('app.env') != 'production'){
            $snappy = App::make('snappy.image');
            $snappy->setOption('width',1125);
            $image = $snappy->getOutput(config('app.url').'/weapp/'.$showUrl.'/'.$submission->id);
            $file_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
            (new UploadFile($file_name,base64_encode($image)))->handle();
            $img_url = Storage::disk('oss')->url($file_name);
            $data = $submission->data;
            $data[$collection] = $img_url;
            $submission->data = $data;
            $submission->save();
        }
        return self::createJsonData(true,['url'=>$submission->data[$collection]]);
    }

    public function albumInfo(Request $request, JWTAuth $JWTAuth) {
        $this->validate($request, [
            'id' => 'required'
        ]);

        $id = $request->input('id');
        $category = Category::find($id);
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'icon' => $category->icon,
            'summary' => $category->summary
        ];
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth) {
            event(new SystemNotify('小程序用户'.$oauth->user_id.'['.$oauth->nickname.']查看专题详情:'.$category->name));
        }
        return self::createJsonData(true,$data);
    }

    public function getAlbumList(Request $request) {
        $categories = Category::where('grade',0)->where('type','product_album')->where('status',1)->orderBy('sort','asc')->orderBy('updated_at','desc')->simplePaginate($request->input('perPage',10));
        $data = $categories->toArray();
        return self::createJsonData(true,$data);
    }

    public function moreAlbum(Request $request) {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $id = $request->input('id');
        $categories = Category::where('grade',0)->where('type','product_album')->where('id','!=',$id)->orderBy('sort','asc')->orderBy('updated_at','desc')->take(8)->get();
        $data = $categories->toArray();
        return self::createJsonData(true,$data);
    }

    public function albumProductList(Request $request,JWTAuth $JWTAuth) {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $oauth = $JWTAuth->parseToken()->toUser();
        $category_id = $request->input('id');
        $query = TagCategoryRel::select(['id','tag_id','support_rate'])->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
        $tags = $query->where('category_id',$category_id)->orderBy('support_rate','desc')->orderBy('updated_at','desc')->simplePaginate(50);
        $return = $tags->toArray();
        $list = [];
        foreach ($tags as $tag) {
            $model = Tag::find($tag->tag_id);
            $info = Tag::getReviewInfo($model->id);
            $can_support = RateLimiter::instance()->getValue('album_product_support',date('Ymd').'_'.$tag->id.'_'.$oauth->user_id);
            $list[] = [
                'id' => $tag->id,
                'tag_id' => $model->id,
                'name' => $model->name,
                'logo' => $model->logo,
                'summary' => $model->summary,
                'support_rate' => $tag->support_rate?:0,
                'review_average_rate' => $info['review_average_rate'],
                'can_support' => $can_support<3?1:0,
                'advance_desc' => $model->getAdvanceDesc()
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    public function commentAlbum(Request $request,JWTAuth $JWTAuth) {
        $this->validate($request, [
            'id' => 'required',
            'body'          => 'required|min:1',
            'parent_id'     => 'required|integer',
        ]);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }

        $category_id = $request->input('id');
        $category = Category::find($category_id);

        $parentComment = ($request->parent_id > 0) ? Comment::find($request->parent_id) : null;
        $data = [
            'content'          => formatContentUrls($request->body),
            'user_id'       => $user->id,
            'parent_id'     => $request->parent_id,
            'level'         => $parentComment ? ($parentComment->level + 1) : 0,
            'source_id' => $category_id,
            'source_type' => get_class($category),
            'to_user_id'  => 0,
            'status'      => 1,
            'supports'    => 0,
        ];
        $data['mentions'] = is_array($request->input('mentions'))?array_unique($request->input('mentions')):[];

        $comment = Comment::create($data);

        return self::createJsonData(true,$comment->toArray(),ApiException::SUCCESS,'评论成功');
    }

    public function albumCommentList(Request $request,JWTAuth $JWTAuth) {
        $this->validate($request, [
            'id' => 'required',
        ]);
        $oauth = $JWTAuth->parseToken()->toUser();

        $category_id = $request->input('id');
        $category = Category::find($category_id);
        if (!$category) {
            throw new ApiException(ApiException::PRODUCT_ALBUM_NOT_EXIST);
        }
        $orderBy = $request->input('order_by',1);
        $query = $category->comments()
            ->where('parent_id', 0);
        if ($orderBy == 1) {
            $query = $query->orderBy('created_at', 'desc');
        } else {
            $query = $query->orderBy('supports', 'desc')->orderBy('created_at', 'desc');
        }
        $comments = $query->paginate($request->input('perPage',20));
        $return = $comments->toArray();
        foreach ($return['data'] as &$item) {
            $this->checkCommentIsSupported($oauth->user_id, $item);
        }

        return self::createJsonData(true,$return);
    }

    public function albumNewsList(Request $request,JWTAuth $JWTAuth) {
        $validateRules = [
            'id' => 'required'
        ];

        $this->validate($request,$validateRules);
        $id = $request->input('id');
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));

        $tagIds = TagCategoryRel::where('category_id',$id)
            ->where('type',TagCategoryRel::TYPE_REVIEW)
            ->where('status',1)->pluck('tag_id')->toArray();
        $tagIds = array_unique($tagIds);
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $news = WechatWenzhangInfo::where('source_type',1)
            ->where('type',WechatWenzhangInfo::TYPE_TAG_NEWS)
            ->whereHas('tags',function($query) use ($tagIds) {
                $query->whereIn('tag_id', $tagIds);
            })
            ->orderBy('date_time','desc')->paginate($perPage);

        $return = $news->toArray();
        $list = [];
        foreach ($news as $new) {
            $list[] = [
                'id' => $new->_id,
                'title' => strip_tags($new->title),
                'type' => 'link',
                'date' => date('Y年m月d日',strtotime($new->date_time)),
                'author' => domain($new->content_url),
                'cover_pic' => $new->cover_url,
                'link_url' => config('app.url').'/articleInfo/'.$new->_id.'?inwehub_user_device=weapp_dianping&source=album_'.$id
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    public function supportAlbumProduct(Request $request,JWTAuth $JWTAuth) {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $id = $request->input('id');
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            throw new ApiException(ApiException::USER_WEAPP_NEED_REGISTER);
        }
        if (RateLimiter::STATUS_BAD == RateLimiter::instance()->increase('album_product_support',date('Ymd').'_'.$id.'_'.$oauth->user_id,60*60*24,3)) {
            return self::createJsonData(true,[],ApiException::PRODUCT_ALBUM_SUPPORT_LIMIT);
        }
        $rel = TagCategoryRel::find($id);
        $data = [
            'user_id'        => $user->id,
            'supportable_id'   => $id,
            'supportable_type' => get_class($rel),
            'refer_user_id'    => $rel->category_id
        ];

        $support = Support::create($data);
        $rel->increment('support_rate');
        $tag_ids = TagCategoryRel::where('category_id',$rel->category_id)->pluck('tag_id')->toArray();
        foreach ($tag_ids as $tag_id) {
            RateLimiter::instance()->hSet('product_pending_update_cache',$tag_id,$tag_id);
        }
        RateLimiter::instance()->setVale('album_share_image',$rel->category_id,'');
        return self::createJsonData(true);
    }

    public function getAlbumSupports(Request $request) {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $id = $request->input('id');
        $supports = Support::where('refer_user_id',$id)->where('supportable_type',TagCategoryRel::class)->take(20)->inRandomOrder()->get();
        $list = [];
        foreach ($supports as $support) {
            $user = User::find($support->user_id);
            $rel = $support->source;
            $tag = Tag::find($rel->tag_id);
            $list[] = [
                'id' => $support->id,
                'user_name' => $user->name,
                'tag_name' => $tag->name,
                'created_at' => (string)$support->created_at
            ];
        }
        return self::createJsonData(true,$list);
    }

    public function getHotProducts(Request $request) {
        $perPage = $request->input('perPage',15);
        $query = TagCategoryRel::selectRaw('sum(support_rate) as total_support_rate,tag_id')->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
        $tags = $query->groupBy('tag_id')->orderBy('total_support_rate','desc')->simplePaginate($perPage);
        $return = $tags->toArray();
        $list = [];
        foreach ($tags as $tag) {
            $model = Tag::find($tag->tag_id);
            $total_support_rate = $tag->total_support_rate;
            $info = Tag::getReviewInfo($model->id);
            $list[] = [
                'id' => $tag->id,
                'tag_id' => $model->id,
                'name' => $model->name,
                'logo' => $model->logo,
                'summary' => $model->summary,
                'support_rate' => $total_support_rate,
                'review_average_rate' => $info['review_average_rate'],
                'advance_desc' => $model->getAdvanceDesc()
            ];
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);
    }

    public function getHotAlbum(Request $request) {
        $list = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_HOT_ALBUM)->orderBy('sort','asc')->get();
        $return = [];
        foreach ($list as $idea) {
            $category = Category::find($idea->source_id);
            $return[] = [
                'id' => $category->id,
                'name' => $category->name,
                'tips' => $idea->content['desc'],
                'sort' => $idea->sort
            ];
        }
        return self::createJsonData(true,$return);
    }

    //统计数据上报
    public function reportActivity(Request $request,JWTAuth $JWTAuth) {
        $this->validate($request, [
            'start_time' => 'required',
            'end_time'   => 'required',
            'page'       => 'required'
        ]);
        \Log::info('reportActivity',$request->all());
        $oauth = $JWTAuth->parseToken()->toUser();
        $this->dispatch(new WeappActivity($oauth->id,$request->all()));
        return self::createJsonData(true);
    }
}