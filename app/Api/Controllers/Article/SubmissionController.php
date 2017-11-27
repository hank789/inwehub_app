<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Services\RateLimiter;
use App\Traits\SubmitSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

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

        $tagString = trim($request->input('tags'));

        if ($request->type == 'link') {
            $this->validate($request, [
                'url'   => 'required|url',
                'title' => 'required|between:1,2000',
            ]);

            //检查url是否重复
            $exist_submission_id = Redis::connection()->hget('voten:submission:url',$request->url);
            if ($exist_submission_id){
                $exist_submission = Submission::find($exist_submission_id);
                if (!$exist_submission) {
                    throw new ApiException(ApiException::ARTICLE_URL_ALREADY_EXIST);
                }
                $exist_submission_url = '/c/'.$exist_submission->category_id.'/'.$exist_submission->slug;
                return self::createJsonData(false,[],500,"您提交的网址已经存在");
            }
            try {
                //$data = $this->linkSubmission($request);
                $img = getUrlImg($request->url);
                $img_url = '';
                if ($img) {
                    //保存图片
                    $img_name = 'submissions/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                    Storage::put($img_name, file_get_contents($img));
                    $img_url = Storage::url($img_name);
                }


                $data = [
                    'url'           => $request->url,
                    'title'         => $request->title,
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => $img_url,
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
                'title' => 'required|between:1,2000',
                'type'  => 'required|in:link,text',
            ]);

            $data = $this->textSubmission($request);
        }

        try {
            $data['current_address_name'] = $request->input('current_address_name');
            $data['current_address_longitude'] = $request->input('current_address_longitude');
            $data['current_address_latitude'] = $request->input('current_address_latitude');

            $submission = Submission::create([
                'title'         => $request->title,
                'slug'          => $this->slug($request->title),
                'type'          => $request->type,
                'category_name' => $category->name,
                'category_id'   => $category->id,
                'rate'          => firstRate(),
                'user_id'       => $user->id,
                'data'          => $data,
            ]);
            if ($request->type == 'link') {
                Redis::connection()->hset('voten:submission:url',$request->url, $submission->id);
            }
            /*添加标签*/
            Tag::multiSaveByIds($tagString,$submission);

        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            throw new ApiException(ApiException::ERROR);
        }

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
        return self::createJsonData(true,['title'=>$title]);
    }

    /**
     * Returns the submission.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBySlug(Request $request)
    {
        $this->validate($request, [
            'slug' => 'required',
        ]);

        $user = $request->user();
        $submission = Submission::where('slug',$request->slug)->first();
        if (!$submission) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $return = $submission->toArray();
        $upvote = Support::where('user_id',$user->id)
            ->where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)
            ->exists();
        $bookmark = Collection::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
            ->exists();
        $attention_user = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($user))->where('source_id','=',$submission->user_id)->first();
        $return['is_followed_author'] = $attention_user ?1 :0;
        $return['is_upvoted'] = $upvote ? 1 : 0;
        $return['is_bookmark'] = $bookmark ? 1: 0;
        $return['is_commented'] = $submission->comments()->where('user_id',$user->id)->exists() ? 1: 0;
        $return['bookmarks'] = Collection::where('source_id',$submission->id)
            ->where('source_type',Submission::class)->count();
        $return['data']['current_address_name'] = $return['data']['current_address_name']??'';
        $return['data']['current_address_longitude'] = $return['data']['current_address_longitude']??'';
        $return['data']['current_address_latitude']  = $return['data']['current_address_latitude']??'';

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