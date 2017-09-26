<?php

namespace App\Models;

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
 */
class Support extends Model
{
    protected $table = 'supports';
    protected $fillable = ['user_id','supportable_id','supportable_type'];

}
