<?php namespace App\Models\Pay;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/5/16 上午10:42
 * @email: wanghui@yonglibao.com
 */
use App\Models\Relations\BelongsToUserTrait;

/**
 * @mixin \Eloquent
 */
class MoneyLog extends Model {
    use BelongsToUserTrait;

    protected $table = 'user_money_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id','source_type','source_id','change_money','io','money_type'];


    const MONEY_TYPE_ASK = 1;
    const MONEY_TYPE_ANSWER = 2;
    const MONEY_TYPE_WITHDRAW = 3;
    const MONEY_TYPE_FEE = 4;

}