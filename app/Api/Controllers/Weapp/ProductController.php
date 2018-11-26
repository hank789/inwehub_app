<?php namespace App\Api\Controllers\Weapp;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Models\Category;
use App\Models\Company\CompanyData;
use App\Models\Doing;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: hank.huiwang@gmail.com
 */

class ProductController extends Controller {

    //产品服务详情
    public function info(Request $request, JWTAuth $JWTAuth) {
        $validateRules = [
            'tag_name' => 'required'
        ];
        $this->validate($request,$validateRules);
        $oauth = $JWTAuth->parseToken()->toUser();
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
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

        $tag = Tag::getTagByName($tag_name);
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
            $user->name = '游客';
        }
        $submission = Submission::where('slug',$request->slug)->first();
        if (!$submission) {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        if ($submission->type != 'review') {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        $actionName = Doing::ACTION_VIEW_DIANPING_REVIEW_INFO;
        $actionUrl = config('app.mobile_url').'#/dianping/comment/'.$submission->slug;
        $return = $this->formatSubmissionInfo($submission,$user);

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

        $query = $submission->comments()
            ->where('parent_id', 0);
        if ($orderBy == 1) {
            $query = $query->orderBy('created_at', 'desc');
        } else {
            $query = $query->orderBy('supports', 'desc');
        }
        $comments = $query->simplePaginate(20);
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
        $submission = $this->storeSubmission($request,$user);
        return self::createJsonData(true,$submission->toArray());
    }

}