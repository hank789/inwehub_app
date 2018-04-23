<?php namespace App\Models\Pay;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/5/16 上午10:42
 * @email: wanghui@yonglibao.com
 */
use App\Models\Relations\BelongsToUserTrait;

/**
 * App\Models\Pay\MoneyLog
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property string $before_money 未交易前账户金额
 * @property string $change_money 交易金额
 * @property int $io 初入账:1入账,-1出账
 * @property int $money_type 资金类型:1提问,2回答,3提现
 * @property int $status 提现状态:0处理中,1处理成功,2处理失败
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereBeforeMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereChangeMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereIo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereMoneyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\MoneyLog whereUserId($value)
 */
class MoneyLog extends Model {
    use BelongsToUserTrait;

    protected $table = 'user_money_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','source_type','source_id','change_money','io','money_type','before_money','status'];


    const MONEY_TYPE_ASK = 1;
    const MONEY_TYPE_ANSWER = 2;
    const MONEY_TYPE_WITHDRAW = 3;
    const MONEY_TYPE_FEE = 4;
    const MONEY_TYPE_PAY_FOR_VIEW_ANSWER = 5;
    const MONEY_TYPE_REWARD = 6;//分红
    const MONEY_TYPE_COUPON = 7;//红包
    const MONEY_TYPE_ASK_PAY_WALLET = 8;//付费问答使用红包支付
    const MONEY_TYPE_SYSTEM_ADD = 9;//系统后台添加

    const STATUS_PROCESS = 0;
    const STATUS_SUCCESS = 1;
    const STATUS_FAIL    = 2;

}