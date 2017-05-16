<?php namespace App\Models\Pay;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/5/16 上午11:07
 * @email: wanghui@yonglibao.com
 */
use App\Models\Relations\BelongsToUserTrait;

/**
 * @mixin \Eloquent
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