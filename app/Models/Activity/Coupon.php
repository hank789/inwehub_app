<?php namespace App\Models\Activity\Coupon;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
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


}
