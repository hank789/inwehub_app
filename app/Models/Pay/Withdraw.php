<?php namespace App\Models\Pay;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\UserOauth;
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


    const WITHDRAW_CHANNEL_WX = 1;//微信app
    const WITHDRAW_CHANNEL_WX_PUB = 2;//微信公众号

    const WITHDRAW_CHANNEL_ALIPAY = 6;


    public function getAccount(){
        switch($this->withdraw_channel){
            case self::WITHDRAW_CHANNEL_WX:
                $user_oauth = UserOauth::where('user_id',$this->user_id)->where('auth_type',UserOauth::AUTH_TYPE_WEIXIN)->where('status',1)->orderBy('updated_at','desc')->first();
                return $user_oauth->nickname;
                break;
            case self::WITHDRAW_CHANNEL_WX_PUB:
                $user_oauth = UserOauth::where('user_id',$this->user_id)->where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)->where('status',1)->orderBy('updated_at','desc')->first();
                return $user_oauth->nickname;
                break;
        }
    }

    public function getWithdrawChannelName(){
        switch($this->withdraw_channel){
            case self::WITHDRAW_CHANNEL_WX:
                return '微信app';
                break;
            case self::WITHDRAW_CHANNEL_WX_PUB:
                return '微信公众号';
                break;
        }
    }

}