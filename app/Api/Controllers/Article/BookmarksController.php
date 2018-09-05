<?php namespace App\Api\Controllers\Article;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Collection;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\UserTag;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/11/14 下午4:39
 * @email: hank.huiwang@gmail.com
 */

class BookmarksController extends Controller {

    /**
     * Favorited submissions by Auth user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function bookmarkSubmission(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
        ]);

        $submission = Submission::find($request->id);
        if (!$submission) {
            throw new ApiException(ApiException::ARTICLE_NOT_EXIST);
        }
        $user = $request->user();

        $group = Group::find($submission->group_id);
        if ($group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
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

        /*不能多次收藏*/
        $userCollect = $user->isCollected(get_class($submission),$submission->id);
        $this->calculationSubmissionRate($submission->id);
        if($userCollect){
            $userCollect->delete();
            $submission->decrement('collections');
            UserTag::multiDecrement($user->id,$submission->tags()->get(),'articles');
            return self::createJsonData(true,['tip'=>'取消收藏成功','type'=>'unbookmarked'],ApiException::SUCCESS,'取消收藏成功');
        }

        $data = [
            'user_id'     => $user->id,
            'source_id'   => $submission->id,
            'source_type' => get_class($submission),
            'subject'  => '',
        ];

        $collect = Collection::create($data);

        if($collect){
            $submission->increment('collections');
        }
        UserTag::multiIncrement($user->id,$submission->tags()->get(),'articles');

        return self::createJsonData(true,['tip'=>'收藏成功', 'type'=>'bookmarked'],ApiException::SUCCESS,'收藏成功');
    }

}