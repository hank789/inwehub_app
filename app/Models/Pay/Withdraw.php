<?php namespace App\Models\Pay;
use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午12:25
 * @email: wanghui@yonglibao.com
 */

/**
 * @mixin \Eloquent
 */
class Withdraw extends Model {
    use BelongsToUserTrait;

    protected $table = 'withdraw';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','user_id', 'order_no','transaction_id','subject','body','amount','return_param',
        'client_ip','response_msg','finish_time','response_data','withdraw_channel','status'];


    const WITHDRAW_STATUS_PENDING = 0;
    const WITHDRAW_STATUS_PROCESS = 1;
    const WITHDRAW_STATUS_SUCCESS = 2;
    const WITHDRAW_STATUS_FAIL    = 3;
}