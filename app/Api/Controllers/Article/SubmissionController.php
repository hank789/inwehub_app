<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\NewSubmissionJob;
use App\Jobs\UploadFile;
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
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use App\Traits\SubmitSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
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

        if (RateLimiter::instance()->increase('submission:store',$user->id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        if ($request->type == 'link') {
            $category = Category::where('slug','channel_xwdt')->first();
        } else {
            $category = Category::where('slug','channel_gddj')->first();
        }
        $category_id = $category->id;

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
        $public = 1;
        if ($request->type != 'review') {
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
            $public = $group->public;
        }

        //点评
        if ($request->type == 'review') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'category_ids' => 'required',
                'tags' => 'required',
                'rate_star' => 'required',
                'identity' => 'required'
            ]);
            $data = $this->uploadImgs($request->input('photos'));
            $data['category_ids'] = $request->input('category_ids');
            $data['author_identity'] = $request->input('identity');
            $category_id = $tagString;
        }

        if ($request->type == 'article') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'description' => 'required',
                'group_id' => 'required|integer'
            ]);
            $description = QuillLogic::parseImages($request->input('description'));
            if ($description === false){
                $description = $request->input('description');
            }
            $img_url = $this->uploadImgs($request->input('photos'));
            $data = [
                'description'   => $description,
                'img'           => $img_url['img']?$img_url['img'][0]:''
            ];
        }

        if ($request->type == 'link') {
            $this->validate($request, [
                'url'   => 'required|url',
                'title' => 'required|between:1,6000',
                'group_id' => 'required|integer'
            ]);

            //检查url是否重复
            $exist_submission_id = Redis::connection()->hget('voten:submission:url',$request->url);
            if ($exist_submission_id && false){
                $exist_submission = Submission::find($exist_submission_id);
                if (!$exist_submission) {
                    throw new ApiException(ApiException::ARTICLE_URL_ALREADY_EXIST);
                }
                $exist_submission_url = '/c/'.$exist_submission->category_id.'/'.$exist_submission->slug;
                return self::createJsonData(false,['exist_url'=>$exist_submission_url],ApiException::ARTICLE_URL_ALREADY_EXIST,"您提交的网址已经存在");
            }
            try {
                $img_url = $this->uploadImgs($request->input('photos'));

                $data = [
                    'url'           => $request->url,
                    'title'         => Cache::get('url_title_'.$request->url,''),
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => ($img_url['img']?$img_url['img'][0]:'')?:Cache::get('url_img_'.$request->url,''),
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
                'group_id' => 'required|integer'
            ]);

            $data = $this->uploadImgs($request->input('photos'));
        }
        if ($request->input('files')) {
            $data['files'] = $this->uploadFile($request->input('files'));
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
                'category_id'   => $category_id,
                'group_id'      => $group_id,
                'public'        => $public,
                'rate'          => firstRate(),
                'rate_star'     => $request->input('rate_star',0),
                'hide'          => $request->input('hide',0),
                'status'        => $request->input('draft',0)?0:1,
                'user_id'       => $user->id,
                'data'          => $data,
                'views'         => 1
            ]);
            if ($request->type == 'link') {
                Redis::connection()->hset('voten:submission:url',$request->url, $submission->id);
            }

            /*添加标签*/
            Tag::multiSaveByIds($tagString,$submission,$request->type == 'review'?'reviews':'');
            if ($newTagString) {
                Tag::multiAddByName($newTagString,$submission);
            }
            UserTag::multiIncrement($user->id,$submission->tags()->get(),'articles');
            if ($submission->status == 1) {
                $this->dispatch((new NewSubmissionJob($submission->id)));
            }

        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            throw new ApiException(ApiException::ERROR);
        }
        self::$needRefresh = true;
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

        $description = QuillLogic::parseImages($request->input('description'));
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
        $return = $submission->toArray();
        if ($submission->group_id) {
            $group = Group::find($submission->group_id);
            $return['group'] = $group->toArray();
            $return['group']['is_joined'] = 1;
            $return['group']['name'] = str_limit($return['group']['name'], 20);
            if ($group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
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
            } else {
                $return['group']['subscribers'] = $group->getHotIndex() + User::count();
            }
        }

        $submission->increment('views');
        $this->calculationSubmissionRate($submission->id);

        $upvote = Support::where('user_id',$user->id)
            ->where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)
            ->exists();
        $downvote = DownVote::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
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
        $return['is_downvoted'] = $downvote ? 1 : 0;
        $return['is_bookmark'] = $bookmark ? 1: 0;
        $return['supporter_list'] = $supporters;
        $return['support_description'] = $downvote?$submission->getDownvoteRateDesc():$submission->getSupportRateDesc($upvote);
        $return += $submission->getSupportTypeTip();
        $return['support_percent'] = $submission->getSupportPercent();
        $return['tags'] = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
        foreach ($return['tags'] as &$tag) {
            $tag['review_count'] = 0;
            $tag['review_average_rate'] = 0;
            if (isset($submission->data['category_ids'])) {
                $reviewInfo = Tag::getReviewInfo($tag['id']);
                $tag['review_count'] = $reviewInfo['review_count'];
                $tag['review_average_rate'] = $reviewInfo['review_average_rate'];
            }
        }
        $return['is_commented'] = $submission->comments()->where('user_id',$user->id)->exists() ? 1: 0;
        $return['bookmarks'] = Collection::where('source_id',$submission->id)
            ->where('source_type',Submission::class)->count();
        $return['data']['current_address_name'] = $return['data']['current_address_name']??'';
        $return['data']['current_address_longitude'] = $return['data']['current_address_longitude']??'';
        $return['data']['current_address_latitude']  = $return['data']['current_address_latitude']??'';
        $img = $return['data']['img']??'';
        if (false && in_array($return['group']['is_joined'],[-1,0,2]) && $img) {
            if (is_array($img)) {
                foreach ($img as &$item) {
                    $item .= '?x-oss-process=image/blur,r_20,s_20';
                }
            } else {
                $img .= '?x-oss-process=image/blur,r_20,s_20';
            }
        }
        $return['data']['img'] = $img;
        $return['related_question'] = null;
        if (isset($return['data']['related_question']) && $return['data']['related_question']) {
            $related_question = Question::find($return['data']['related_question']);
            $answer_uids = Answer::where('question_id',$related_question->id)->take(3)->pluck('user_id')->toArray();
            $answer_users = [];
            foreach ($answer_uids as $answer_uid) {
                $answer_user = User::find($answer_uid);
                $answer_users[] = [
                    'uuid' => $answer_user->uuid,
                    'avatar' => $answer_user->avatar
                ];
            }
            $return['related_question'] = [
                'id' => $related_question->id,
                'question_type' => $related_question->question_type,
                'price'      => $related_question->price,
                'title'  => $related_question->title,
                'tags' => $related_question->tags()->wherePivot('is_display',1)->get()->toArray(),
                'status' => $related_question->status,
                'status_description' => $related_question->price.'元',
                'follow_number' => $related_question->followers,
                'answer_number' => $related_question->answers,
                'answer_users'  => $answer_users
            ];
        }
        if ($submission->hide) {
            //匿名
            $return['owner']['avatar'] = config('image.user_default_avatar');
            $return['owner']['name'] = '匿名';
            $return['owner']['id'] = '';
            $return['owner']['uuid'] = '';
            $return['owner']['is_expert'] = 0;
        }
        if ($submission->type == 'review') {
            $tag = Tag::find($submission->category_id);
            $return['related_tags'] = $tag->relationReviews(4);
        }

        //seo信息
        $keywords = array_unique(explode(',',$submission->data['keywords']??''));
        $return['seo'] = [
            'title' => strip_tags($submission->type == 'link' ? $submission->data['title'] : $submission->title),
            'description' => strip_tags($submission->title),
            'keywords' => implode(',',array_slice($keywords,0,5)),
            'published_time' => (new Carbon($submission->created_at))->toAtomString()
        ];

        $this->logUserViewTags($user->id,$submission->tags()->get());
        $this->doing($user,Doing::ACTION_VIEW_SUBMISSION,get_class($submission),$submission->id,$submission->type == 'link'?$submission->data['title']:$submission->title,
            '',0,0,'',config('app.mobile_url').'#/c/'.$submission->category_id.'/'.$submission->slug);
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