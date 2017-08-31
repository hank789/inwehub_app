<?php namespace App\Models\Activity;

use App\Models\Pay\Ordergable;
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
