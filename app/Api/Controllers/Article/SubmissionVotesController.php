<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Jobs\NotifyInwehub;
use App\Models\Readhub\ReadHubUser;
use App\Models\Readhub\Submission;
use App\Models\Readhub\SubmissionDownvotes;
use App\Models\Readhub\SubmissionUpvotes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/11/14 下午3:28
 * @email: wanghui@yonglibao.com
 */

class SubmissionVotesController extends Controller {


    /**
     * updates the vote rocords of the user in Redis.
     *
     * @param int   $voter_id
     * @param mixed $previous_vote
     * @param int   $submission_id
     *
     * @return void
     */
    protected function updateUserUpVotesRecords($voter_id, $author_id, $previous_vote, $submission_id)
    {

        // remove the $submission_id from the array
        if ($previous_vote == 'upvote') {
            $this->updateSubmissionKarma($author_id, -1);
        }

        // remove the $submission_id from the downvotes array and add it to the upvotes array
        if ($previous_vote == 'downvote') {
            $this->updateSubmissionKarma($author_id, 2);
        }

        // add the $submission_id to the array
        if ($previous_vote == null || empty($previous_vote)) {
            $this->updateSubmissionKarma($author_id, 1);
        }
    }

    /**
     * updates the vote rocords of the user in Redis.
     *
     * @param int   $voter_id
     * @param int   $author_id
     * @param mixed $previous_vote
     * @param int   $submission_id
     *
     * @return void
     */
    protected function updateUserDownVotesRecords($voter_id, $author_id, $previous_vote, $submission_id)
    {
        // remove the $submission_id from the array
        if ($previous_vote == 'downvote') {
            $this->updateSubmissionKarma($author_id, 1);
        }

        // remove the $submission_id from the downvotes array and add it to the upvotes array
        if ($previous_vote == 'upvote') {
            $this->updateSubmissionKarma($author_id, -2);
        }

        // add the $submission_id to the array
        if (empty($previous_vote)) {
            $this->updateSubmissionKarma($author_id, -1);
        }
    }

    /**
     * updates the submission_karma of the author user.
     *
     * @param int $id
     * @param int $number
     *
     * @return void
     */
    protected function updateSubmissionKarma($id, $number)
    {

        $user = ReadHubUser::find($id);
        $newKarma = Redis::hincrby('user.'.$id.'.data', 'submissionKarma', $number);

        // for newbie users we update on each new vote, but for major ones, we do this once a 50 times
        if ($newKarma < 100 || ($newKarma % 20) === 0) {
            $user->submission_karma = $newKarma;
            $user->save();
            return;
        }
    }

    /**
     * Adds the upvote record for the auth user and (if the user is not trying to cheat) updates the vote points and rate
     * for the submission model.
     *
     */
    public function upVote(Request $request)
    {
        $this->validate($request, [
            'submission_id' => 'required|integer',
        ]);

        $user = $request->user();
        $submission = Submission::find($request->submission_id);
        $downvote = SubmissionDownvotes::where('user_id',$user->id)
            ->where('submission_id',$submission->id)->first();

        $upvote = SubmissionUpvotes::where('user_id',$user->id)
            ->where('submission_id',$submission->id)->first();

        $previous_vote = null;
        $type = 'upvote';
        try {
            if ($upvote) {
                //之前是赞，再请求一次是取消赞
                $previous_vote = 'upvote';
                $type = 'cancel_upvote';
                $new_upvotes = ($submission->upvotes - 1);
                $upvote->delete();
            } elseif ($downvote) {
                //之前是踩，再请求一次是赞
                $previous_vote = 'downvote';
                $new_upvotes = ($submission->upvotes + 1);
                $new_downvotes = ($submission->downvotes - 1);
                $downvote->delete();
                SubmissionUpvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'submission_id' => $submission->id
                ]);
            } else {
                $new_upvotes = ($submission->upvotes + 1);
                SubmissionUpvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'submission_id' => $submission->id
                ]);
            }

            $this->updateUserUpVotesRecords(
                $user->id, $submission->user_id, $previous_vote, $request->submission_id
            );
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return self::createJsonData(false,[],500,$e->getMessage());
        }

        $submission->upvotes = $new_upvotes ?? $submission->upvotes;
        $submission->downvotes = $new_downvotes ?? $submission->downvotes;
        $submission->rate = rateSubmission($new_upvotes ?? $submission->upvotes, $new_downvotes ?? $submission->downvotes, $submission->created_at);

        $submission->save();


        $voted = Redis::connection()->hget('voten:submission:upvote',$submission->id.'_'.$user->id);
        if (!$voted) {
            dispatch((new NotifyInwehub($user->id,'NewSubmissionUpVote',['submission_id'=>$submission->id]))->onQueue('inwehub:default'));
            Redis::connection()->hset('voten:submission:upvote',$submission->id.'_'.$user->id,1);
        }


        return self::createJsonData(true,['type'=>$type]);
    }

    /**
     * Adds the downvote record for the auth user and (if the user is not trying to cheat) updates the vote points and rate
     * for the submission model.
     *
     */
    public function downVote(Request $request)
    {
        $this->validate($request, [
            'submission_id' => 'required|integer',
        ]);

        $user = $request->user();
        $submission = Submission::find($request->submission_id);
        $downvote = SubmissionDownvotes::where('user_id',$user->id)
            ->where('submission_id',$submission->id)->first();

        $upvote = SubmissionUpvotes::where('user_id',$user->id)
            ->where('submission_id',$submission->id)->first();

        $previous_vote = '';
        $type = 'downvote';
        try {
            if ($downvote) {
                //之前是踩，再请求一次就是取消踩
                $new_downvotes = ($submission->downvotes - 1);
                $downvote->delete();
                $previous_vote = 'downvote';
                $type = 'cancel_downvote';
            } elseif ($upvote) {
                //之前是赞，再请求一次是踩
                $previous_vote = 'upvote';
                $new_downvotes = ($submission->downvotes + 1);
                $new_upvotes = ($submission->upvotes - 1);
                $upvote->delete();
                SubmissionDownvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'submission_id' => $submission->id
                ]);
            } else {
                $new_downvotes = ($submission->downvotes + 1);
                SubmissionDownvotes::create([
                    'user_id' => $user->id,
                    'ip_address' => getRequestIpAddress(),
                    'submission_id' => $submission->id
                ]);
            }

            $this->updateUserDownVotesRecords($user->id, $submission->user_id, $previous_vote, $request->submission_id);
        } catch (\Exception $e) {
            app('sentry')->captureException($e);
            return self::createJsonData(false,[],500,$e->getMessage());
        }

        $submission->upvotes = $new_upvotes ?? $submission->upvotes;
        $submission->downvotes = $new_downvotes ?? $submission->downvotes;
        $submission->rate = rateSubmission($new_upvotes ?? $submission->upvotes, $new_downvotes ?? $submission->downvotes, $submission->created_at);

        $submission->save();

        return self::createJsonData(true,['type'=>$type]);
    }

}