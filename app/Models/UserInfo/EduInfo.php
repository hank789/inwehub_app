<?php namespace App\Models\UserInfo;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
 */
class EduInfo extends Model
{
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'user_edu_info';
    protected $fillable = ['user_id', 'school','major','degree','begin_time','end_time','description'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
