<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */



use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\User;
use Illuminate\Console\Command;

class AddUserToGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:addUserToGroup {groupId} {uid?} {expert?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将指定用户加入指定圈子中';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $groupId = $this->argument('groupId');
        $userId = $this->argument('uid');
        $expert = $this->argument('expert');
        $group = Group::find($groupId);
        if ($userId) {
            $uids = [$userId];
        } else {
            if ($expert) {
                $uids = User::where('is_expert',1)->pluck('id')->toArray();
            } else {
                $uids = User::pluck('id')->toArray();
            }
        }
        foreach ($uids as $uid) {
            GroupMember::firstOrCreate([
                'user_id' => $uid,
                'group_id' => $groupId
            ],
                [
                    'user_id' => $uid,
                    'group_id' => $groupId,
                    'audit_status'=>Group::AUDIT_STATUS_SUCCESS
                ]);
        }
        $group->subscribers = GroupMember::where('group_id',$group->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->count();
        $group->save();
    }

}