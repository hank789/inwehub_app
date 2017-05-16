<?php namespace App\Models\Pay;
/**
 * @author: wanghui
 * @date: 2017/5/16 上午11:24
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 */
class Ordergable extends model {
    protected $table = 'pay_order_gables';

    protected $fillable = ['pay_order_gable_type', 'pay_order_gable_id', 'pay_order_id'];
}