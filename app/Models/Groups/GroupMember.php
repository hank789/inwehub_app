<?php

namespace App\Models\Groups;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\Groups\GroupMember
 *
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property int $audit_status 审核状态，0待审核，1审核通过，2审核不通过
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Groups\Group $group
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\GroupMember whereAuditStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\GroupMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\GroupMember whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\GroupMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\GroupMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\GroupMember whereUserId($value)
 * @mixin \Eloquent
 */
class GroupMember extends Model
{
    use BelongsToUserTrait;

    protected $table = 'group_members';
    protected $fillable = ['user_id','group_id','audit_status'];

    const AUDIT_STATUS_DRAFT = 0;
    const AUDIT_STATUS_SUCCESS = 1;
    const AUDIT_STATUS_REJECT = 2;

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }
}
