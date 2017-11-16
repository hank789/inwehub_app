<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\NotifyInwehub;
use App\Models\Readhub\Comment;
use App\Models\Readhub\CommentUpvotes;
use App\Models\Readhub\Submission;
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
        if (RateLimiter::instance()->increase('submission:comment:store',$user->id)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $submission = Submission::find($request->submission_id);
        $parentComment = ($request->parent_id > 0) ? Comment::find($request->parent_id) : null;

        $comment = Comment::create([
            'body'          => $request->body,
            'user_id'       => $user->id,
            'category_id'   => $submission->category_id,
            'parent_id'     => $request->parent_id,
            'level'         => $request->parent_id == 0 ? 0 : ($parentComment->level + 1),
            'submission_id' => $submission->id,
            'rate'          => firstRate(),
            'upvotes'       => 1,
            'downvotes'     => 0,
            'edited_at'     => null,
        ]);

        dispatch((new NotifyInwehub($user->id,'NewComment',['commnet_id'=>$comment->id]))->onQueue('inwehub:default'));

        $this->firstVote($user, $comment->id);

        // set proper relation values:
        $comment->owner = $user;
        $comment->children = [];

        return self::createJsonData(true,$comment->toArray());
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

        if ($request->sort == 'new') {
            $comments = $submission->comments()
                ->where('parent_id', 0)
                ->orderBy('created_at', 'desc')
                ->simplePaginate(20);
        } else {
            // Sort by default which is 'hot'
            $comments = $submission->comments()
                ->where('parent_id', 0)
                ->orderBy('rate', 'desc')
                ->simplePaginate(20);
        }
        return self::createJsonData(true,$comments->toArray());
    }

    /**
     * Up-votes on comment.
     *
     * @param collection $user
     * @param int        $comment_id
     *
     * @return void
     */
    protected function firstVote($user, $comment_id)
    {
        CommentUpvotes::create([
            'user_id' => $user->id,
            'ip_address' => getRequestIpAddress(),
            'comment_id' => $comment_id
        ]);
    }

    /**
     * Patches the comment record.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patch(Request $request)
    {
        $this->validate($request, [
            'comment_id' => 'required|integer',
            'body'       => 'required',
        ]);

        $comment = Comment::findOrFail($request->comment_id);

        abort_unless($this->mustBeOwner($comment), 403);

        // make sure the body has changed
        if ($request->body == $comment->body) {
            return response('回复内容未有变更', 422);
        }

        $comment->update([
            'body'      => $request->body,
            'edited_at' => Carbon::now(),
        ]);

        return response('回复修改成功', 200);
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