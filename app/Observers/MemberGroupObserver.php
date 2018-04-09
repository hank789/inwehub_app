<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Models\Groups\GroupMember;
use App\Notifications\GroupMemberApplyResult;
use App\Notifications\NewGroupMemberApply;
use App\Notifications\NewGroupMemberJoin;
use Illuminate\Contracts\Queue\ShouldQueue;

class MemberGroupObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function created(GroupMember $member){
        $group = $member->group;
        switch ($member->audit_status) {
            case GroupMember::AUDIT_STATUS_DRAFT:
                $user = $group->user;
                $user->notify(new NewGroupMemberApply($user->id,$member));
                break;
            case GroupMember::AUDIT_STATUS_SUCCESS:
                if ($group->public) {
                    //公开圈子
                    $group->user->notify(new NewGroupMemberJoin($group->user_id,$member));
                } else {
                    $member->user->notify(new GroupMemberApplyResult($member->user_id,$member));
                }
                break;
            case GroupMember::AUDIT_STATUS_REJECT:
                $member->user->notify(new GroupMemberApplyResult($member->user_id,$member));
                break;
        }
    }

    public function updated(GroupMember $member){
        $this->updated($member);
    }

}