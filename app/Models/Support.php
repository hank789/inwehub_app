<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Support
 *
 * @property int $id
 * @property string $session_id
 * @property int $user_id
 * @property int $supportable_id
 * @property string $supportable_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereSessionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereSupportableId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereSupportableType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Support whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User|null $user
 * @property int $refer_user_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Support whereReferUserId($value)
 */
class Support extends Model
{
    use BelongsToUserTrait;

    protected $table = 'supports';
    protected $fillable = ['user_id','supportable_id','supportable_type','refer_user_id'];

    public function source()
    {
        return $this->morphTo('supportable');
    }

}
