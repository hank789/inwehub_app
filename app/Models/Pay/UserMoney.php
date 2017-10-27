<?php namespace App\Models\Pay;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/5/16 上午11:07
 * @email: wanghui@yonglibao.com
 */
use App\Models\Relations\BelongsToUserTrait;

/**
 * App\Models\Pay\UserMoney
 *
 * @mixin \Eloquent
 * @property int $user_id
 * @property float $total_money 总金额
 * @property float $settlement_money 结算中的金额
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\UserMoney whereSettlementMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\UserMoney whereTotalMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\UserMoney whereUserId($value)
 */
class UserMoney extends Model {
    use BelongsToUserTrait;

    protected $table = 'user_money';
    public $timestamps = false;
    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','total_money'];



}