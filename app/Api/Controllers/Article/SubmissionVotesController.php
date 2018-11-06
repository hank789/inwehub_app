<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\NotifyInwehub;
use App\Models\DownVote;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/11/14 下午3:28
 * @email: hank.huiwang@gmail.com
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

        $user = User::find($id);
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

        if (RateLimiter::instance()->increase('support:submission',$submission->id.'_'.$user->id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $group = Group::find($submission->group_id);
        if ($submission->group_id && $group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$submission->group_id)->first();
            $is_joined = -1;
            if ($groupMember) {
                $is_joined = $groupMember->audit_status;
            }
            if ($user->id == $group->user_id) {
                $is_joined = 3;
            }
            if (in_array($is_joined,[-1,0,2])) {
                return self::createJsonData(false,['group_id'=>$group->id],ApiException::GROUP_NOT_JOINED,ApiException::$errorMessages[ApiException::GROUP_NOT_JOINED]);
            }
        }

        //已经踩过，不能点赞
        $downvote = DownVote::where("user_id",'=',$user->id)->where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->first();
        if ($downvote) {
            throw new ApiException(ApiException::USER_SUPPORT_ALREADY_DOWNVOTE);
        }

        $previous_vote = null;
        /*再次点赞相当于是取消点赞*/
        $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($submission))->where('supportable_id','=',$submission->id)->first();
        if($support){
            $previous_vote = 'upvote';
            $support->delete();
            $submission->decrement('upvotes');
            return self::createJsonData(true,['tip'=>'取消点赞成功','type'=>'cancel_upvote','support_description'=>$submission->getSupportRateDesc(false),
                'support_percent'=>$submission->getSupportPercent()],ApiException::SUCCESS,'取消点赞成功');
        }

        $data = [
            'user_id'        => $user->id,
            'supportable_id'   => $submission->id,
            'supportable_type' => get_class($submission),
            'refer_user_id'    => $submission->user_id
        ];

        $support = Support::create($data);

        if($support){
            $submission->increment('upvotes');
        }

        $this->updateUserUpVotesRecords(
            $user->id, $submission->user_id, $previous_vote, $request->submission_id
        );
        $this->calculationSubmissionRate($submission->id);
        UserTag::multiIncrement($user->id,$submission->tags()->get(),'articles');

        return self::createJsonData(true,['tip'=>'点赞成功','type'=>'upvote','support_description'=>$submission->getSupportRateDesc(),
            'support_percent'=>$submission->getSupportPercent()],ApiException::SUCCESS,'点赞成功');
    }

    public function downVote(Request $request)
    {
        $this->validate($request, [
            'submission_id' => 'required|integer',
        ]);

        $user = $request->user();
        $submission = Submission::find($request->submission_id);

        if (RateLimiter::instance()->increase('down:submission',$submission->id.'_'.$user->id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $group = Group::find($submission->group_id);
        if ($submission->group_id && $group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$submission->group_id)->first();
            $is_joined = -1;
            if ($groupMember) {
                $is_joined = $groupMember->audit_status;
            }
            if ($user->id == $group->user_id) {
                $is_joined = 3;
            }
            if (in_array($is_joined,[-1,0,2])) {
                return self::createJsonData(false,['group_id'=>$group->id],ApiException::GROUP_NOT_JOINED,ApiException::$errorMessages[ApiException::GROUP_NOT_JOINED]);
            }
        }

        $support = Support::where("user_id",'=',$user->id)->where('supportable_type','=',get_class($submission))->where('supportable_id','=',$submission->id)->first();
        if ($support) {
            throw new ApiException(ApiException::USER_DOWNVOTE_ALREADY_SUPPORT);
        }

        $previous_vote = null;
        /*再次踩相当于是取消踩*/
        $downvote = DownVote::where("user_id",'=',$user->id)->where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->first();
        if($downvote){
            $previous_vote = 'downvote';
            $downvote->delete();
            $submission->decrement('downvotes');
            return self::createJsonData(true,['tip'=>'取消踩成功','type'=>'cancel_downvote','support_description'=>$submission->getSupportRateDesc(false),
                'support_percent'=>$submission->getSupportPercent()],ApiException::SUCCESS,'取消踩成功');
        }

        $data = [
            'user_id'        => $user->id,
            'source_id'   => $submission->id,
            'source_type' => get_class($submission),
            'refer_user_id'    => $submission->user_id
        ];

        $downvote = DownVote::create($data);

        if($downvote){
            $submission->increment('downvotes');
        }

        $this->updateUserUpVotesRecords(
            $user->id, $submission->user_id, $previous_vote, $request->submission_id
        );
        $this->calculationSubmissionRate($submission->id);

        return self::createJsonData(true,['tip'=>'踩成功','type'=>'downvote','support_description'=>$submission->getDownvoteRateDesc(),
            'support_percent'=>$submission->getSupportPercent()],ApiException::SUCCESS,'踩成功');
    }

}