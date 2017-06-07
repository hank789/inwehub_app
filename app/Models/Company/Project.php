<?php namespace App\Models\Company;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
 */
class Project extends Model
{
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'projects';
    protected $fillable = ['user_id', 'project_name','project_amount','project_province','project_city','company_name','status','description'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
