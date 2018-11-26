<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

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
 * @property string $openid
 * @property string|null $unionid
 * @property string|null $scope
 * @property string|null $full_info
 * @property int $status 状态:0未生效,1已生效
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOauth whereFullInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOauth whereOpenid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOauth whereScope($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOauth whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserOauth whereUnionid($value)
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
    protected $fillable = ['id','user_id', 'access_token','refresh_token','expires_in','auth_type','nickname','avatar','scope','full_info','status','openid','unionid'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
        'full_info' => 'json'
    ];


    const AUTH_TYPE_WEIXIN = 'weixinapp';
    const AUTH_TYPE_WEAPP  = 'weapp';//微信小程序-项目招募助手
    const AUTH_TYPE_WEIXIN_GZH  = 'weixin_gzh';//微信公众号
    const AUTH_TYPE_WEAPP_ASK  = 'weapp_ask';//微信小程序-点评


}
