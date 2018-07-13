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
 * App\Models\Pay\Withdraw
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $order_no
 * @property string|null $transaction_id
 * @property string $amount 提现金额
 * @property string|null $return_param 请求自定义参数
 * @property string $client_ip
 * @property string|null $response_msg 第三方响应信息
 * @property string|null $finish_time 提现完成时间,Y-m-d H:i:s
 * @property mixed|null $response_data 第三方返回完整信息
 * @property int $withdraw_channel 提现方式:1微信,2支付宝
 * @property int $status 提现状态:0待处理,1处理中,2处理成功,3处理失败
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereClientIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereFinishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereResponseMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereReturnParam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Withdraw whereWithdrawChannel($value)
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
                return $user_oauth?$user_oauth->nickname:'';
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