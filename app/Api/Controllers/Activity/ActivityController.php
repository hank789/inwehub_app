<?php namespace App\Api\Controllers\Activity;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Article;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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
        $is_mine = $request->input('is_mine');

        if ($is_mine) {
            $articles = Collection::leftJoin('articles','collections.source_id','=','articles.id')
                ->where('collections.user_id',$request->user()->id)
                ->where('articles.category_id',$category->id)
                ->where('collections.source_type','App\Models\Article')
                ->select('articles.*','collections.status as c_status')
                ->orderBy('articles.id','DESC')
                ->paginate(Config::get('api_data_page_size'));
            $return = $articles->toArray();
        } else {
            $articles = Article::where('category_id',$category->id)->where('status','>',0)->orderBy('articles.id','DESC')->paginate(Config::get('api_data_page_size'));
            $return = $articles->toArray();
        }

        $return['data'] = [];

        foreach ($articles as $article) {
            $status = $article->status;
            $c_status = -1;
            if (!isset($article->c_status)) {
                $userCollect = $request->user()->isCollected('App\Models\Article',$article->id);
                if ($userCollect) {
                    $c_status = $userCollect->status;
                }
            } else {
                $c_status = $article->c_status;
            }
            switch ($c_status) {
                case Collection::COLLECT_STATUS_PENDING:
                    // 报名处理中
                    $status = 3;
                    break;
                case Collection::COLLECT_STATUS_VERIFY:
                    // 报名成功
                    $status = 4;
                    break;
                case Collection::COLLECT_STATUS_REJECT:
                    // 报名失败
                    if ($status != Article::ARTICLE_STATUS_CLOSED) $status = 5;
                    break;
                case Collection::COLLECT_STATUS_NEED_RE_ENROLL:
                    // 重新报名
                    if ($status != Article::ARTICLE_STATUS_CLOSED) $status = 6;
                    break;
            }
            $return['data'][] = [
                'id' => $article->id,
                'image_url' => $article->logo,
                'title'     => $article->title,
                'status'      => $status,
                'created_at'  => date('Y/m/d',strtotime($article->created_at))
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
        if (false === judge_user_activity_level($user->userData->user_level,'activity_enroll')) {
            throw new ApiException(ApiException::ACTIVITY_PERMISSION_LIMIT);
        }
        $data = $request->all();

        $source  = Article::find($data['activity_id']);
        if (!$source) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        if ($source->status == Article::ARTICLE_STATUS_CLOSED) {
            throw  new ApiException(ApiException::ACTIVITY_TIME_OVER);
        }
        if ($source->deadline && time()>= strtotime($source->deadline)){
            $source->status = Article::ARTICLE_STATUS_CLOSED;
            $source->save();
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

        if ($source->status != Article::ARTICLE_STATUS_CLOSED && $source->deadline && time()>= strtotime($source->deadline)){
            $source->status = Article::ARTICLE_STATUS_CLOSED;
            $source->save();
        }

        $status = $source->status;
        $info = [
            'id' => $source->id,
            'image_url' => $source->logo,
            'title'     => $source->title,
            'description' => $source->content,
            'status'      => $status,
            'created_at'  => date('Y/m/d',strtotime($source->created_at))
        ];
        $userCollect = $request->user()->isCollected(get_class($source),$source->id);
        $feedback = [
            'description' => ''
        ];
        if ($userCollect) {
            $feedback['description'] = $userCollect->subject;
            switch ($userCollect->status) {
                case Collection::COLLECT_STATUS_PENDING:
                    // 报名处理中
                    $status = 3;
                    break;
                case Collection::COLLECT_STATUS_VERIFY:
                    // 报名成功
                    $status = 4;
                    break;
                case Collection::COLLECT_STATUS_REJECT:
                    // 报名失败
                    if ($status != Article::ARTICLE_STATUS_CLOSED) $status = 5;
                    break;
                case Collection::COLLECT_STATUS_NEED_RE_ENROLL:
                    // 重新报名
                    if ($status != Article::ARTICLE_STATUS_CLOSED) $status = 6;
                    break;
            }
            $info['status'] = $status;
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
        $user_id = $request->user()->id;
        $comments = $source->comments()->where(function ($query) use ($user_id) {
            $query->where('user_id',$user_id)->orWhere('to_user_id',$user_id);
        })->orderBy('created_at','desc')->simplePaginate(Config::get('api_data_page_size'));
        $return = $comments->toArray();
        $return['data'] = [];

        foreach ($comments as $comment) {
            $return['data'][] = [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'user_name' => $comment->user->name,
                'user_avatar_url' => $comment->user->avatar,
                'content'   => $comment->content,
                'created_at' => date('Y/m/d H:i',strtotime($comment->created_at))
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

        return self::createJsonData(true,[
            'tips'=>'评论成功',
            'content' => $data['content'],
            'created_at' => date('Y/m/d H:i',strtotime($comment->created_at)),
            'user_name'  => $request->user()->name
        ]);
    }

}