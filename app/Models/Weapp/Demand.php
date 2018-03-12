<?php namespace App\Models\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午6:49
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;

/**
 * Class Demand
 *
 * @package App\Models\Weapp
 * @property int $id
 * @property int $user_id
 * @property string $title 标题
 * @property string $address 地点
 * @property float $salary 薪资
 * @property string $industry 行业
 * @property int $project_cycle 项目周期
 * @property string $project_begin_time 项目开始时间
 * @property string $description 需求描述
 * @property string $expired_at 过期时间
 * @property int $views 查看次数
 * @property int $status 状态:0,待发布,1已发布,2被拒绝,3已关闭，4已过期
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereIndustry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereProjectBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereProjectCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\Demand whereViews($value)
 * @mixin \Eloquent
 */
class Demand extends Model
{
    use BelongsToUserTrait;
    protected $table = 'demand';
    protected $fillable = ['title', 'user_id', 'salary', 'industry', 'project_cycle', 'project_begin_time', 'description', 'expired_at', 'views','address', 'status'];

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISH = 1;
    const STATUS_REJECT = 2;
    const STATUS_CLOSED = 3;
    const STATUS_EXPIRED = 4;

}