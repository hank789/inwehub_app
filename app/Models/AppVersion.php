<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * App\Models\XsSearch
 *
 * @mixin \Eloquent
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
