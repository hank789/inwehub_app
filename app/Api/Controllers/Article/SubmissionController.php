<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\LogUserViewTags;
use App\Jobs\NewSubmissionJob;
use App\Logic\QuillLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Doing;
use App\Models\DownVote;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Question;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use App\Traits\SubmitSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/11/13 下午5:51
 * @email: hank.huiwang@gmail.com
 */

class SubmissionController extends Controller {

    use SubmitSubmission;

    public function store(Request $request)
    {
        $user = $request->user();
        $submission = $this->storeSubmission($request,$user);
        return self::createJsonData(true,$submission->toArray());
    }

    public function update(Request $request) {
        $this->validate($request, [
            'slug' => 'required',
            'title' => 'required|between:1,6000',
            'description' => 'required',
            'group_id' => 'required|integer'
        ]);
        $submission = Submission::where('slug',$request->slug)->first();
        $user = $request->user();
        if ($submission->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        if ($submission->type != 'article') {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $group_id = $request->input('group_id',0);
        $group = Group::find($group_id);
        if ($group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
            if ($group->audit_status != Group::AUDIT_STATUS_SUCCESS) {
                throw new ApiException(ApiException::GROUP_UNDER_AUDIT);
            }
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group_id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();
            if (!$groupMember) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        }
        $oldStatus = $submission->status;

        $description = QuillLogic::parseImages($request->input('description'),false);
        if ($description === false){
            $description = $request->input('description');
        }
        $img_url = $this->uploadImgs($request->input('photos'));
        $object_data = $submission->data;
        $object_data['description'] = $description;
        $object_data['img'] = $img_url['img']?$img_url['img'][0]:'';

        $submission->title = $request->input('title');
        $submission->group_id = $request->input('group_id');
        $submission->status = $request->input('draft',0)?0:1;
        $submission->data = $object_data;
        $submission->save();
        if ($oldStatus == 0 && $submission->status == 1) {
            $this->dispatch((new NewSubmissionJob($submission->id)));
        }

        self::$needRefresh = true;
        return self::createJsonData(true);
    }

    public function uploadImage(Request $request){
        $validateRules = [
            'id' => 'required|integer',
            'photos'=> 'required'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $data = $request->all();
        $submission = Submission::find($data['id']);
        if ($submission->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $data = $submission->data;
        $img = $this->uploadImgs($request->input('photos'));
        $data['img'] += $img['img'];
        $submission->data = $data;
        $submission->save();
        return self::createJsonData(true,['id'=>$submission->id]);
    }

    public function thirdApiStore(Request $request) {
        $uid = $request->input('token');
        if (!$uid) {
            return self::createJsonData(false);
        }
        $user = User::where('uuid',$uid)->first();
        if (!$user) {
            return self::createJsonData(false);
        }
        if (RateLimiter::instance()->increase('submission:store',$user->id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        if (!$user->isRole('operatormanager')) {
            return self::createJsonData(false);
        }

        $category = Category::find($request->input('category_id',0));
        if (!$category) {
            if ($request->type == 'link') {
                $category = Category::where('slug','channel_xwdt')->first();
            } else {
                $category = Category::where('slug','channel_gddj')->first();
            }
        }

        $tagString = $request->input('tags');
        $newTagString = $request->input('new_tags');
        if ($newTagString) {
            if (is_array($newTagString)) {
                foreach ($newTagString as $s) {
                    if (strlen($s) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
                }
            } else {
                if (strlen($newTagString) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
            }
        }
        $group_name = $request->input('group_name','小哈公社');
        $group = Group::where('name',$group_name)->first();
        $url = $request->input('url');
        $title = $request->input('title');
        if ($request->type == 'link') {
            $this->validate($request, [
                'url'   => 'required|url'
            ]);
            try {
                $info = getUrlInfo($url,true);
                $url_title = $info['title'];
                $img_url = $info['img_url'];
                if (empty($title)) $title = $url_title;
                $data = [
                    'url'           => $url,
                    'title'         => $url_title,
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => $img_url,
                    'thumbnail'     => null,
                    'providerName'  => null,
                    'publishedTime' => null,
                    'domain'        => domain($url),
                ];
                Redis::connection()->hset('voten:submission:url',$url,1);
            } catch (\Exception $e) {
                $data = [
                    'url'           => $url,
                    'title'         => $title,
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => null,
                    'thumbnail'     => null,
                    'providerName'  => null,
                    'publishedTime' => null,
                    'domain'        => domain($url),
                ];
            }
        }

        try {
            $data['current_address_name'] = $request->input('current_address_name');
            $data['current_address_longitude'] = $request->input('current_address_longitude');
            $data['current_address_latitude'] = $request->input('current_address_latitude');
            $data['mentions'] = is_array($request->input('mentions'))?array_unique($request->input('mentions')):[];
            $operator_id = $request->input('user_id');
            $submission = Submission::create([
                'title'         => formatContentUrls($title),
                'slug'          => $this->slug($url_title),
                'type'          => 'link',
                'category_name' => $category->name,
                'category_id'   => $category->id,
                'group_id'      => $group->id,
                'public'        => $group->public,
                'rate'          => firstRate(),
                'status'        => 1,
                'user_id'       => $operator_id?:$user->id,
                'data'          => $data,
                'views'         => 1
            ]);

            if ($request->type == 'link'||true) {
                Redis::connection()->hset('voten:submission:url',$url, $submission->id);
            }
            /*添加标签*/
            Tag::multiSaveByIds($tagString,$submission);
            if ($newTagString) {
                Tag::multiAddByName($newTagString,$submission);
            }
            UserTag::multiIncrement($user->id,$submission->tags()->get(),'articles');
            if ($submission->status == 1) {
                $this->dispatch(new NewSubmissionJob($submission->id));
            }
            event(new SystemNotify('通过workflow发布分享:'.$title));

        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            throw new ApiException(ApiException::ERROR);
        }
        self::$needRefresh = true;
        return self::createJsonData(true,$submission->toArray());
    }


    /**
     * Fetches the title from an external URL.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string title
     */
    public function getTitleAPI(Request $request)
    {
        $this->validate($request, [
            'url' => 'required|url',
        ]);

        $info = getUrlInfo($request->url,true);

        return self::createJsonData(true,$info);
    }


    public function setSupportType(Request $request) {
        $this->validate($request, [
            'submission_id' => 'required',
            'support_type' => 'required|in:1,2,3,4',
        ]);
        $submission = Submission::find($request->input('submission_id'));
        if (!$submission) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $submission->support_type = $request->input('support_type');
        $submission->save();
        return self::createJsonData(true);
    }

    /**
     * Returns the submission.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBySlug(Request $request,JWTAuth $JWTAuth)
    {
        $this->validate($request, [
            'slug' => 'required',
        ]);

        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $submission = Submission::where('slug',$request->slug)->first();
        if (!$submission) {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        $actionName = Doing::ACTION_VIEW_SUBMISSION;
        $actionUrl = config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug;
        if ($submission->type == 'review') {
            $actionName = Doing::ACTION_VIEW_DIANPING_REVIEW_INFO;
            $actionUrl = config('app.mobile_url').'#/dianping/comment/'.$submission->slug;
        }
        $return = $this->formatSubmissionInfo($request,$submission,$user);

        $this->doing($user,$actionName,get_class($submission),$submission->id,$submission->type == 'link'?$submission->data['title']:$submission->title,
            '',0,0,'',$actionUrl);
        return self::createJsonData(true,$return);
    }



    /**
     * Destroys the submisison record from the database.
     *
     * @param \Illuminate\Http\Request $request
     *
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        $submission = Submission::findOrFail($request->id);
        $user = $request->user();
        if ($submission->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        if ($submission->type == 'link') {
            Redis::connection()->hdel('voten:submission:url',$submission->data['url']);
        }
        $submission->delete();
        self::$needRefresh = true;
        return self::createJsonData(true);
    }

    //推荐文章
    public function recommendSubmission(Request $request){
        $this->validate($request, [
            'submission_id' => 'required|integer',
        ]);
        $submission = Submission::findOrFail($request->submission_id);
        if ($submission->recommend_status == 0){
            $submission->recommend_status = 1;
            $submission->save();
        }

        return self::createJsonData(true);
    }

}