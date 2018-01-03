<?php namespace App\Models\Pay;
/**
 * @author: wanghui
 * @date: 2017/5/15 下午8:45
 * @email: wanghui@yonglibao.com
 */

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Pay\Order
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property string $order_no
 * @property string|null $transaction_id
 * @property string $subject
 * @property string|null $body 支付详情
 * @property string $amount 支付金额
 * @property string $actual_amount
 * @property string|null $return_param 请求自定义参数
 * @property string $client_ip
 * @property string|null $response_msg 第三方响应信息
 * @property string|null $finish_time 支付完成时间,Y-m-d H:i:s
 * @property mixed|null $response_data 第三方返回完整信息
 * @property int $pay_channel 支付方式:1微信app支付,2微信公众号支付,3微信扫码支付,4微信刷卡支付,5微信小程序支付,6微信wap支付,7支付宝app支付
 * @property int $status 订单状态:0待支付,1支付处理中,2支付成功,3支付失败
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereActualAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereClientIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereFinishTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order wherePayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereResponseMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereReturnParam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Order whereUserId($value)
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
    protected $fillable = ['id','user_id', 'order_no','transaction_id','subject','body','amount','actual_amount','return_param',
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
    const PAY_STATUS_QUIT    = 4;


    const PAY_CHANNEL_WX_APP = 1;
    const PAY_CHANNEL_WX_PUB = 2;
    const PAY_CHANNEL_WX_QR = 3;
    const PAY_CHANNEL_WX_BAR = 4;
    const PAY_CHANNEL_WX_LITE = 5;
    const PAY_CHANNEL_WX_WAP = 6;

    const PAY_CHANNEL_ALIPAY_APP = 7;

    const PAY_CHANNEL_IOS_IAP = 11;

    public function questions()
    {
        return $this->morphedByMany('App\Models\Question', 'pay_order_gable',null,'pay_order_id');
    }

    public function answer()
    {
        return $this->morphedByMany('App\Models\Answer', 'pay_order_gable',null,'pay_order_id');
    }

    public function getPayChannelName(){
        switch ($this->pay_channel){
            case self::PAY_CHANNEL_WX_APP:
                return '微信APP';
                break;
            case self::PAY_CHANNEL_WX_PUB:
                return '微信公众号';
                break;
            case self::PAY_CHANNEL_WX_QR:
                break;
            case self::PAY_CHANNEL_WX_BAR:
                break;
            case self::PAY_CHANNEL_WX_LITE:
                return '微信小程序';
                break;
            case self::PAY_CHANNEL_WX_WAP:
                break;
            case self::PAY_CHANNEL_ALIPAY_APP:
                break;
            case self::PAY_CHANNEL_IOS_IAP:
                return '苹果';
                break;
        }
    }

}
