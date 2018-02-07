<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Collection
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property string $subject
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereUserId($value)
 * @mixin \Eloquent
 * @property int $status
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Collection whereStatus($value)
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 */
class Collection extends Model
{
    use BelongsToUserTrait;

    protected $table = 'collections';
    protected $fillable = ['user_id','source_id','source_type','subject'];

    const COLLECT_STATUS_PENDING = 1;//待审核
    const COLLECT_STATUS_VERIFY = 2;//审核通过
    const COLLECT_STATUS_NEED_RE_ENROLL = 3;//审核不通过，可重新报名
    const COLLECT_STATUS_REJECT = 4;//审核不通过，不可重新报名

    public function source()
    {
        return $this->morphTo();
    }
}
