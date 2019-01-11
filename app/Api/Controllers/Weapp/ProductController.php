<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Category;
use App\Models\Company\CompanyData;
use App\Models\Doing;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use App\Traits\SubmitSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        $data = $this->getTagProductInfo($tag);
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
        $query = Submission::where('status',1)->where('category_id',$tag->id);
        $submissions = $query->orderBy('is_recommend','desc')->orderBy('id','desc')->paginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $list[] = $submission->formatListItem($user, false);
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
            $query = $query->orderBy('supports', 'desc');
        }
        $comments = $query->simplePaginate($request->input('perPage',20));
        $return = $comments->toArray();
        $return['total'] = $submission->comments_number;
        foreach ($return['data'] as &$item) {
            $this->checkCommentIsSupported($user, $item);
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
        $result = $this->storeSubmission($request,$user);
        return self::createJsonData(true,$result);
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

    public function getProductShareImage(Request $request){
        $validateRules = [
            'id'   => 'required|integer',
            'type' => 'required|in:1,2'
        ];
        $this->validate($request,$validateRules);
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
        $tag = Tag::findOrFail($request->input('id'));

        if($tag->getMedia($collection)->isEmpty()){
            $snappy = App::make('snappy.image');
            $snappy->setOption('width',1125);
            $image = $snappy->getOutput(config('app.url').'/weapp/'.$showUrl.'/'.$tag->id);
            $tag->addMediaFromBase64(base64_encode($image))->toMediaCollection($collection);
        }
        $tag = Tag::find($request->input('id'));
        $url = $tag->getMedia($collection)->last()->getUrl();
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

}