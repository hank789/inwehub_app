<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * App\Models\XsSearch
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $app_version
 * @property string|null $package_url
 * @property int $is_ios_force 是否强更:0非强更,1强更
 * @property int $is_android_force 是否android强更:0非强更,1强更
 * @property string|null $update_msg 更新内容
 * @property int $status 状态:0未生效,1已生效
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereIsAndroidForce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereIsIosForce($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion wherePackageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereUpdateMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\AppVersion whereUserId($value)
 */
class AppVersion extends Model
{
    use BelongsToUserTrait;
    protected $table = 'app_version';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','user_id', 'app_version','package_url','is_ios_force','is_android_force','update_msg','status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
