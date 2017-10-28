<?php namespace App\Models\UserInfo;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string|null $school 学校
 * @property string|null $major 专业
 * @property string|null $degree 学历
 * @property string|null $begin_time 开始时间,格式:Y-m
 * @property string $end_time 结束时间,格式:Y-m
 * @property string|null $description 描述
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\EduInfo onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereDegree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereMajor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\EduInfo whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\EduInfo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\EduInfo withoutTrashed()
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
