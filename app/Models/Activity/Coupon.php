<?php namespace App\Models\Activity;

use App\Models\Pay\Ordergable;
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
 * @property int $coupon_type 红包类型:1首次提问
 * @property string $coupon_value 红包金额
 * @property int $coupon_status 红包状态:1未使用 2已使用 3已过期 默认为1
 * @property string|null $expire_at 过期时间
 * @property int|null $days 有效期
 * @property string $used_at 使用日期
 * @property int|null $used_object_id 使用对象id
 * @property string|null $used_object_type 使用对象类型
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Activity\Coupon onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereCouponStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereCouponType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereCouponValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereExpireAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereUsedObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereUsedObjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Activity\Coupon whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Activity\Coupon withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Activity\Coupon withoutTrashed()
 */
class Coupon extends Model
{
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'coupons';
    protected $fillable = ['user_id', 'coupon_type','coupon_value','coupon_status','expire_at','days','used_at',
        'used_object_id','used_object_type'];


    const COUPON_TYPE_FIRST_ASK = 1;//首次提问


    const COUPON_STATUS_PENDING = 1;
    const COUPON_STATUS_USED = 2;
    const COUPON_STATUS_EXPIRED = 3;


    public function getCouponTypeName(){
        switch($this->coupon_type){
            case self::COUPON_TYPE_FIRST_ASK:
                return '首次提问1元';
                break;
        }
    }

    public function getObjectTypeLink(){
        switch($this->used_object_type){
            case 'App\Models\Pay\Order':
                $orderGable = Ordergable::where('pay_order_id',$this->used_object_id)->first();
                if (!$orderGable) return '#';
                switch($orderGable->pay_order_gable_type){
                    case 'App\Models\Question':
                        return route('ask.question.detail',['id'=>$orderGable->pay_order_gable_id]);
                        break;
                }
                break;
        }
        return '';
    }
}
