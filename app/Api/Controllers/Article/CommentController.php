<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Comment;
use App\Models\Submission;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/11/14 下午12:09
 * @email: wanghui@yonglibao.com
 */

class CommentController extends Controller {

    /**
     * Stores the submitted comment.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Support\Collection $comment
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'body'          => 'required|min:2',
            'parent_id'     => 'required|integer',
            'submission_id' => 'required|integer',
        ]);
        $user = $request->user();
        if (RateLimiter::instance()->increase('submission:comment:store',$user->id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $submission = Submission::find($request->submission_id);
        $parentComment = ($request->parent_id > 0) ? Comment::find($request->parent_id) : null;

        $comment = Comment::create([
            'content'          => $request->body,
            'user_id'       => $user->id,
            'parent_id'     => $request->parent_id,
            'level'         => $request->parent_id == 0 ? 0 : ($parentComment->level + 1),
            'source_id' => $submission->id,
            'source_type' => get_class($submission),
            'to_user_id'  => 0,
            'status'      => 1,
            'supports'    => 0
        ]);

        return self::createJsonData(true,$comment->toArray(),ApiException::SUCCESS,'评论成功');
    }

    /**
     * Paginates the comments of a submission.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'submission_slug' => 'required',
            'sort'            => 'required',
        ]);

        $submission = Submission::where('slug',$request->submission_slug)->first();

        $comments = $submission->comments()->with('owner','children')
            ->where('parent_id', 0)
            ->orderBy('created_at', 'desc')
            ->simplePaginate(20);

        return self::createJsonData(true,$comments->toArray());
    }

    /**
     * Destroys the comment record from the database.
     *
     * @param \Illuminate\Http\Request $request
     *
     */
    public function destroy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        $comment = Comment::find($request->id);
        $submission = Submission::find($comment->submission_id);
        $user = $request->user();
        if ($comment->user_id != $user->id) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }

        $comment->delete();
        $submission->decrement('comments_number');

        return self::createJsonData(true);

    }

}