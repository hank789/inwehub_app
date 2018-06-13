<?php

namespace App\Models\Groups;

use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Models\Groups\Group
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $description
 * @property string $logo
 * @property int $public
 * @property int $audit_status 审核状态:0待审核，1审核通过，2审核不通过
 * @property int $subscribers 订阅人数
 * @property int $articles 贴子数
 * @property string $failed_reason
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Groups\GroupMember[] $members
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Groups\Group onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereArticles($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereAuditStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereFailedReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereSubscribers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Groups\Group whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Groups\Group withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Groups\Group withoutTrashed()
 * @mixin \Eloquent
 */
class Group extends Model
{
    use BelongsToUserTrait;

    protected $table = 'groups';
    protected $fillable = ['user_id','name','description','logo','public','audit_status','subscribers', 'top', 'articles','failed_reason'];


    const AUDIT_STATUS_DRAFT = 0;
    const AUDIT_STATUS_SUCCESS = 1;
    const AUDIT_STATUS_REJECT = 2;
    const AUDIT_STATUS_SYSTEM = 3;//系统圈子，不需要用户加入就可访问，默认为私有圈子
    const AUDIT_STATUS_CLOSED = 4;//已关闭

    public function members() {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public static function search($word)
    {
        $list = self::where('name','like',"%$word%");
        return $list;
    }

    /**
     * 人气：总人数+动态数+点赞数+评论数+群聊条数
     */
    public function getHotIndex(){
        $upvotes = Submission::where('group_id',$this->id)->sum('upvotes');
        $commnets = Submission::where('group_id',$this->id)->sum('comments_number');
        $messages = 0;
        $room = Room::where('r_type',2)
            ->where('source_id',$this->id)
            ->where('source_type',Group::class)
            ->where('status',Room::STATUS_OPEN)->first();
        if ($room) {
            $messages = MessageRoom::where('room_id',$room->id)->count();
        }
        return $this->subscribers + $this->articles + $upvotes + $commnets + $messages;
    }

}
