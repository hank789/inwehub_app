<?php namespace App\Models\Pay;
/**
 * @author: wanghui
 * @date: 2017/5/15 下午8:45
 * @email: wanghui@yonglibao.com
 */

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 */
class Order extends Model
{
    use BelongsToUserTrait;
    protected $table = 'pay_order';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','user_id', 'order_no','transaction_id','subject','body','amount','return_param',
        'client_ip','response_msg','finish_time','response_data','pay_channel','status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    const PAY_STATUS_PENDING = 0;
    const PAY_STATUS_PROCESS = 1;
    const PAY_STATUS_SUCCESS = 2;
    const PAY_STATUS_FAIL    = 3;


    const PAY_CHANNEL_WX_APP = 1;
    const PAY_CHANNEL_WX_PUB = 2;
    const PAY_CHANNEL_WX_QR = 3;
    const PAY_CHANNEL_WX_BAR = 4;
    const PAY_CHANNEL_WX_LITE = 5;
    const PAY_CHANNEL_WX_WAP = 6;

    const PAY_CHANNEL_ALIPAY_APP = 7;

    public function questions()
    {
        return $this->morphedByMany('App\Models\Question', 'pay_order_gable');
    }

}
