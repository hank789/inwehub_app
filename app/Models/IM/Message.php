<?php

namespace App\Models\IM;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Attention
 *
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @mixin \Eloquent
 * @property array $data
 * @property string|null $read_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Message whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Message whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\IM\Message whereUserId($value)
 */
class Message extends Model
{
    use BelongsToUserTrait;
    protected $table = 'im_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'user_id', 'read_at',
    ];

    protected $casts = [
        'data' => 'json',
    ];

}
