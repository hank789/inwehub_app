<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Models\Readhub\Comment;
use App\Models\Readhub\CommentDownvotes;
use App\Models\Readhub\CommentUpvotes;
use App\Models\Readhub\ReadHubUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/11/14 下午4:20
 * @email: wanghui@yonglibao.com
 */

class CommentVotesController extends Controller {

    /**
     * updates the vote rocords of the user in Redis.
     *
     * @param int   $voter_id
     * @param mixed $previous_vote
     * @param int   $comment_id
     *
     * @return void
     */
    protected function updateUserUpVotesRecords($voter_id, $author_id, $previous_vote, $comment_id)
    {

        // remove the $comment_id from the array
        if ($previous_vote == 'upvote') {
            $this->updateCommentKarma($author_id, -1);
        }

        // remove the $comment_id from the downvotes array and add it to the upvotes array
        if ($previous_vote == 'downvote') {

            $this->updateCommentKarma($author_id, 2);
        }

        // add the $comment_id to the array
        if ($previous_vote == null) {
            $this->updateCommentKarma($author_id, 1);
        }
    }

    /**
     * updates the comment_karma of the author user.
     *
     * @param int $id
     * @param int $number
     *
     * @return void
     */
    protected function updateCommentKarma($id, $number)
    {
        $user = ReadHubUser::find($id);

        $newKarma = Redis::hincrby('user.'.$id.'.data', 'commentKarma', $number);

        // for newbie users we update on each new vote,but for major ones, we do this once a 20 times
        if ($newKarma < 100 || ($newKarma % 20) === 0) {
            $user->comment_karma = $newKarma;
            $user->save();
            return;
        }
    }

    /**
     * updates the vote rocords of the user in Redis.
     *
     * @param int   $voter_id
     * @param int   $author_id
     * @param mixed $previous_vote
     * @param int   $comment_id
     *
     * @return void
     */
    protected function updateUserDownVotesRecords($voter_id, $author_id, $previous_vote, $comment_id)
    {

        // remove the $comment_id from the array
        if ($previous_vote == 'downvote') {
            $this->updateCommentKarma($author_id, 1);
        }

        // remove the $comment_id from the downvotes array and add it to the upvotes array
        if ($previous_vote == 'upvote') {

            $this->updateCommentKarma($author_id, -2);
        }

        // add the $comment_id to the array
        if ($previous_vote == null) {
            $this->updateCommentKarma($author_id, -1);
        }

    }

    /**
     * Adds the upvote record for the auth user and (if the user is not trying to cheat) updates the vote points and rate
     * for the comment model.
     *
     */
    public function upVote(Request $request)
    {
        $this->validate($request, [
            'comment_id' => 'required|integer',
        ]);

        $user = $request->user();
        $comment = Comment::find($request->comment_id);
        $downvote = CommentDownvotes::where('user_id',$user->id)
            ->where('comment_id',$comment->id)->first();

        $upvote = CommentUpvotes::where('user_id',$user->id)
            ->where('comment_id',$comment->id)->first();

        $previous_vote = null;

        try {
            if ($upvote) {
                //如果之前是赞，再请求一次是取消赞
                $previous_vote = 'upvote';
                $new_upvotes = ($comment->upvotes - 1);
                CommentUpvotes::where('user_id',$user->id)
                    ->where('comment_id',$comment->id)->delete();
            } elseif ($downvote) {
                //之前是踩，再请求一次是赞
                $previous_vote = 'downvote';
                $new_upvotes = ($comment->upvotes + 1);
                $new_downvotes = ($comment->downvotes - 1);
                CommentDownvotes::where('user_id',$user->id)
                    ->where('comment_id',$comment->id)->delete();
                CommentUpvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'comment_id' => $comment->id
                ]);
            } else {
                $new_upvotes = ($comment->upvotes + 1);
                CommentUpvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'comment_id' => $comment->id
                ]);
            }

            $this->updateUserUpVotesRecords(
                $user->id, $comment->user_id, $previous_vote, $request->comment_id
            );
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return self::createJsonData(false,[],500,$e->getMessage());
        }

        $comment->upvotes = $new_upvotes ?? $comment->upvotes;
        $comment->downvotes = $new_downvotes ?? $comment->downvotes;
        $comment->rate = rateSubmission($new_upvotes ?? $comment->upvotes, $new_downvotes ?? $comment->downvotes, $comment->created_at);

        $comment->save();


        return self::createJsonData(true);
    }

    /**
     * Adds the downvote record for the auth user and (if the user is not trying to cheat) updates the vote points and rate
     * for the comment model.
     *
     */
    public function downVote(Request $request)
    {
        $this->validate($request, [
            'comment_id' => 'required|integer',
        ]);

        $user = $request->user();
        $comment = Comment::find($request->comment_id);
        $downvote = CommentDownvotes::where('user_id',$user->id)
            ->where('comment_id',$comment->id)->first();

        $upvote = CommentUpvotes::where('user_id',$user->id)
            ->where('comment_id',$comment->id)->first();

        $previous_vote = null;

        try {
            if ($downvote) {
                //之前是踩，再请求一次就是取消踩
                $previous_vote = 'downvote';
                $new_downvotes = ($comment->downvotes - 1);
                CommentDownvotes::where('user_id',$user->id)
                    ->where('comment_id',$comment->id)->delete();
            } elseif ($upvote) {
                //之前是赞，再请求一次是踩
                $previous_vote = 'upvote';
                $new_downvotes = ($comment->downvotes + 1);
                $new_upvotes = ($comment->upvotes - 1);
                CommentUpvotes::where('user_id',$user->id)
                    ->where('comment_id',$comment->id)->delete();
                CommentDownvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'comment_id' => $comment->id
                ]);
            } else {
                $new_downvotes = ($comment->downvotes + 1);
                CommentDownvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'comment_id' => $comment->id
                ]);
            }

            $this->updateUserDownVotesRecords($user->id, $comment->user_id, $previous_vote, $request->comment_id);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return self::createJsonData(false,[],500,$e->getMessage());
        }

        $comment->upvotes = $new_upvotes ?? $comment->upvotes;
        $comment->downvotes = $new_downvotes ?? $comment->downvotes;
        $comment->rate = rateSubmission($new_upvotes ?? $comment->upvotes, $new_downvotes ?? $comment->downvotes, $comment->created_at);

        $comment->save();

        return self::createJsonData(true);
    }

}