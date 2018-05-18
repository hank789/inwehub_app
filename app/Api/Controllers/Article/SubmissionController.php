<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Doing;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use App\Traits\SubmitSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/11/13 下午5:51
 * @email: wanghui@yonglibao.com
 */

class SubmissionController extends Controller {

    use SubmitSubmission;

    /**
     * Stores the submitted submission into database. There are 3 types of submissions:
     * 1.text, 2.link and 3.img. 4.gif Different actions are required for different
     * types. After storing the submission, redirects to the submission page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (RateLimiter::instance()->increase('submission:store',$user->id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
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

        if ($request->type == 'link') {
            $this->validate($request, [
                'url'   => 'required|url',
                'title' => 'required|between:1,6000',
                'group_id' => 'required|integer'
            ]);

            //检查url是否重复
            $exist_submission_id = Redis::connection()->hget('voten:submission:url',$request->url);
            if ($exist_submission_id){
                $exist_submission = Submission::find($exist_submission_id);
                if (!$exist_submission) {
                    throw new ApiException(ApiException::ARTICLE_URL_ALREADY_EXIST);
                }
                $exist_submission_url = '/c/'.$exist_submission->category_id.'/'.$exist_submission->slug;
                return self::createJsonData(false,['exist_url'=>$exist_submission_url],ApiException::ARTICLE_URL_ALREADY_EXIST,"您提交的网址已经存在");
            }
            try {
                $img_url = $this->uploadFile($request->input('photos'));

                $data = [
                    'url'           => $request->url,
                    'title'         => $request->title,
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => $img_url['img']?$img_url['img'][0]:'',
                    'thumbnail'     => null,
                    'providerName'  => null,
                    'publishedTime' => null,
                    'domain'        => domain($request->url),
                ];
                Redis::connection()->hset('voten:submission:url',$request->url,1);
            } catch (\Exception $e) {
                $data = [
                    'url'           => $request->url,
                    'title'         => $request->title,
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => null,
                    'thumbnail'     => null,
                    'providerName'  => null,
                    'publishedTime' => null,
                    'domain'        => domain($request->url),
                ];
            }
        }

        if ($request->type == 'text') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'type'  => 'required|in:link,text',
                'group_id' => 'required|integer'
            ]);

            $data = $this->uploadFile($request->input('photos'));
        }

        try {
            $data['current_address_name'] = $request->input('current_address_name');
            $data['current_address_longitude'] = $request->input('current_address_longitude');
            $data['current_address_latitude'] = $request->input('current_address_latitude');
            $data['mentions'] = is_array($request->input('mentions'))?array_unique($request->input('mentions')):[];

            $submission = Submission::create([
                'title'         => formatContentUrls($request->title),
                'slug'          => $this->slug($request->title),
                'type'          => $request->type,
                'category_name' => $category->name,
                'category_id'   => $category->id,
                'group_id'      => $request->input('group_id'),
                'public'        => $group->public,
                'rate'          => firstRate(),
                'user_id'       => $user->id,
                'data'          => $data,
            ]);
            $group->increment('articles');
            RateLimiter::instance()->sClear('group_read_users:'.$group->id);
            if ($request->type == 'link') {
                Redis::connection()->hset('voten:submission:url',$request->url, $submission->id);
            }
            /*添加标签*/
            Tag::multiSaveByIds($tagString,$submission);
            if ($newTagString) {
                Tag::multiAddByName($newTagString,$submission);
            }
            UserTag::multiIncrement($user->id,$submission->tags()->get(),'articles');

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

        $title = $this->getTitle($request->url);
        $img = getUrlImg($request->url);
        $img_url = '';
        if ($img) {
            //保存图片
            $img_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
            dispatch((new UploadFile($img_name,base64_encode(file_get_contents($img)))));
            $img_url = Storage::url($img_name);
        }
        return self::createJsonData(true,['title'=>$title,'img_url'=>$img_url]);
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
        }
        $submission = Submission::where('slug',$request->slug)->first();
        if (!$submission) {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        $return = $submission->toArray();

        $group = Group::find($submission->group_id);
        $return['group'] = $group->toArray();
        $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
        $return['group']['is_joined'] = -1;
        if ($groupMember) {
            $return['group']['is_joined'] = $groupMember->audit_status;
        }
        if ($user->id == $group->user_id) {
            $return['group']['is_joined'] = 3;
        }
        $return['group']['subscribers'] = $group->getHotIndex();

        if ($group->public == 0 && in_array($return['group']['is_joined'],[-1,0,2]) ) {
            //私有圈子
            return self::createJsonData(true,$return);
        }

        $submission->increment('views');

        $upvote = Support::where('user_id',$user->id)
            ->where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)
            ->exists();
        $bookmark = Collection::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
            ->exists();
        $support_uids = Support::where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)->take(20)->pluck('user_id');
        $supporters = [];
        if ($support_uids) {
            foreach ($support_uids as $support_uid) {
                $supporter = User::find($support_uid);
                $supporters[] = [
                    'name' => $supporter->name,
                    'uuid' => $supporter->uuid
                ];
            }
        }
        $attention_user = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$submission->user_id)->first();
        $return['is_followed_author'] = $attention_user ?1 :0;
        $return['is_upvoted'] = $upvote ? 1 : 0;
        $return['is_bookmark'] = $bookmark ? 1: 0;
        $return['supporter_list'] = $supporters;
        $return['tags'] = $submission->tags()->get()->toArray();
        $return['is_commented'] = $submission->comments()->where('user_id',$user->id)->exists() ? 1: 0;
        $return['bookmarks'] = Collection::where('source_id',$submission->id)
            ->where('source_type',Submission::class)->count();
        $return['data']['current_address_name'] = $return['data']['current_address_name']??'';
        $return['data']['current_address_longitude'] = $return['data']['current_address_longitude']??'';
        $return['data']['current_address_latitude']  = $return['data']['current_address_latitude']??'';
        $img = $return['data']['img']??'';
        if (in_array($return['group']['is_joined'],[-1,0,2]) && $img) {
            if (is_array($img)) {
                foreach ($img as &$item) {
                    $item .= '?x-oss-process=image/blur,r_20,s_20';
                }
            } else {
                $img .= '?x-oss-process=image/blur,r_20,s_20';
            }
        }
        $return['data']['img'] = $img;
        $this->logUserViewTags($user->id,$submission->tags()->get());
        $this->doing($user->id,Doing::ACTION_VIEW_SUBMISSION,get_class($submission),$submission->id,'查看动态');
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