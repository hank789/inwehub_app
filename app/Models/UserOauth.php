<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\UserOauth
 *
 * @property string $id
 * @property string $auth_type
 * @property string $nickname
 * @property string $avatar
 * @property int $user_id
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires_in
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereAccessToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereAuthType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereAvatar($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereExpiresIn($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereNickname($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereRefreshToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserOauth whereUserId($value)
 * @mixin \Eloquent
 */
class UserOauth extends Model
{
    use BelongsToUserTrait;
    protected $table = 'user_oauth';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','user_id', 'access_token','refresh_token','expires_in','auth_type','nickname','avatar'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];






}
