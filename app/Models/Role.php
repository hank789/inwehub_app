<?php
/**
 * Created by PhpStorm.
 * User: sdf_sky
 * Date: 16/6/20
 * Time: 下午6:14
 */

namespace App\Models;


use App\Exceptions\ApiException;
use Bican\Roles\Models\Role as BicanRole;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property int $level
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Bican\Roles\Models\Permission[] $permissions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 */
class Role extends BicanRole
{
    protected $table = 'roles';
    protected $fillable = ['id', 'name','description','slug','level'];


    public static function customerService(){
        return self::where('slug','customerservice');
    }

    public static function getCustomerUserId(){
        $uid = Cache::get('role_customer_uid');
        if (!$uid) {
            //客服
            $role = Role::customerService()->first();
            $role_user = RoleUser::where('role_id',$role->id)->first();
            if (!$role_user) {
                throw new ApiException(ApiException::ERROR);
            }
            $uid = $role_user->user_id;
            Cache::put('role_customer_uid',$uid);
        }
        return $uid;
    }

}