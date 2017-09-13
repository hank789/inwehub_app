<?php namespace App\Api\Controllers\Activity;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Article;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Comment;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/7/13 上午11:30
 * @email: wanghui@yonglibao.com
 */

class ActivityController extends Controller {


    public function index(Request $request) {
        $validateRules = [
            'activity_type'    => 'required|in:1,2'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();

        switch ($data['activity_type']) {
            case 1:
                // 活动
                $category = Category::where("slug","=",'activity_enroll')->first();
                $currentCategoryId = $category->id;
                break;
            case 2:
                // 机遇
                $category = Category::where("slug","=",'project_enroll')->first();
                break;
            default:
                $category = '';
                break;
        }
        if(!$category){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $articles = Article::newest($category->id,10);
        $return = $articles->toArray();
        $return['data'] = [];

        foreach ($articles as $article) {
            $return['data'][] = [
                'id' => $article->id,
                'image_url' => $article->logo,
                'title'     => $article->title,
                'status'      => $article->status,
                'created_at'  => $article->created_at
            ];
        }
        return self::createJsonData(true, $return);
    }

    public function enroll(Request $request){
        $validateRules = [
            'activity_id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $data = $request->all();

        $source  = Article::find($data['activity_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        if ($source->status == 2) {
            throw  new ApiException(ApiException::ACTIVITY_TIME_OVER);
        }
        $subject = '';

        /*不能多次收藏*/
        $userCollect = $user->isCollected(get_class($source),$data['activity_id']);
        if($userCollect){
            return self::createJsonData(true,['tip'=>'报名成功']);
        }

        $data = [
            'user_id'     => $user->id,
            'source_id'   => $data['activity_id'],
            'source_type' => get_class($source),
            'subject'  => $subject,
        ];

        $collect = Collection::create($data);

        if($collect){
            $source->increment('collections');
        }

        return self::createJsonData(true,['tip'=>'报名成功']);
    }

    public function detail(Request $request) {
        $validateRules = [
            'activity_id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();

        $source  = Article::find($data['activity_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        $info = [
            'id' => $source->id,
            'image_url' => $source->logo,
            'title'     => $source->title,
            'description' => $source->content,
            'status'      => $source->status,
            'created_at'  => $source->created_at
        ];
        $userCollect = $request->user()->isCollected(get_class($source),$source->id);
        $feedback = [
            'status' => 0,
            'description' => ''
        ];
        if ($userCollect) {
            $feedback['description'] = $userCollect->subject;
            $feedback['status'] = $userCollect->status;
        }

        return self::createJsonData(true, ['info'=>$info,'feedback'=>$feedback]);

    }

    public function commentList(Request $request){
        $validateRules = [
            'activity_id'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $data = $request->all();

        $source  = Article::find($data['activity_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        $comments = $source->comments()->orderBy('created_at','desc')->simplePaginate(10);
        $return = $comments->toArray();
        $return['data'] = [];

        foreach ($comments as $comment) {
            $return['data'][] = [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'user_name' => $comment->user->name,
                'user_avatar_url' => $comment->user->avatar,
                'content'   => $comment->content,
                'created_at' => $comment->created_at
            ];
        }

        return self::createJsonData(true,  $return);
    }

    public function commentStore(Request $request){
        /*问题创建校验*/
        $validateRules = [
            'activity_id'    => 'required|integer',
            'content' => 'required|max:10000',
        ];

        $this->validate($request,$validateRules);
        $data = $request->all();

        $source  = Article::find($data['activity_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $data = [
            'user_id'     => $request->user()->id,
            'content'     => $data['content'],
            'source_id'   => $data['activity_id'],
            'source_type' => get_class($source),
            'to_user_id'  => 0,
            'status'      => 1,
            'supports'    => 0
        ];


        $comment = Comment::create($data);
        /*问题、回答、文章评论数+1*/
        $comment->source()->increment('comments');

        return self::createJsonData(true,['tips'=>'评论成功']);
    }

}