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
 * @property string|null $certificate 证书,认证名称
 * @property string|null $agency 机构名
 * @property string|null $get_time 获取日期
 * @property string|null $description 描述
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\TrainInfo onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereAgency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereGetTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserInfo\TrainInfo whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\TrainInfo withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserInfo\TrainInfo withoutTrashed()
 */
class TrainInfo extends Model
{
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'user_train_info';
    protected $fillable = ['user_id', 'certificate','agency','get_time','description'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}
