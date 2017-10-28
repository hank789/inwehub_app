<?php namespace App\Models\UserInfo;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string|null $project_name 项目名称
 * @property string|null $title 项目职位
 * @property string|null $customer_name 客户名称
 * @property string|null $begin_time 开始时间,格式:Y-m
 * @property string $end_time 结束时间,格式:Y-m
 * @property string|null $description 描述
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\ProjectInfo onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereProjectName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\ProjectInfo whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\ProjectInfo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\ProjectInfo withoutTrashed()
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
