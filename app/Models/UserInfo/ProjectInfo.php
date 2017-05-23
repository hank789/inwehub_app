<?php namespace App\Models\UserInfo;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
 */
class ProjectInfo extends Model
{
    use BelongsToUserTrait,MorphManyTagsTrait,SoftDeletes;
    protected $table = 'user_project_info';
    protected $fillable = ['user_id', 'project_name','customer_name','title','begin_time','end_time','description'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
