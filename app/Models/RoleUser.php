<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/6/20
 * Time: 下午6:14
 */

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RoleUser
 *
 * @property int $id
 * @property string $role_id
 * @property string $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereLevel($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RoleUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RoleUser whereUserId($value)
 */
class RoleUser extends Model
{
    use BelongsToUserTrait;
    protected $table = 'role_user';

    protected $fillable = ['role_id','user_id','created_at','updated_at'];

}