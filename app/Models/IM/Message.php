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
