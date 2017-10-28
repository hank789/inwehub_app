<?php namespace App\Models\Pay;
/**
 * @author: wanghui
 * @date: 2017/5/16 上午11:24
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Pay\Ordergable
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $pay_order_id
 * @property int $pay_order_gable_id
 * @property string $pay_order_gable_type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Ordergable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Ordergable wherePayOrderGableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Ordergable wherePayOrderGableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Ordergable wherePayOrderId($value)
 */
class Ordergable extends model {
    protected $table = 'pay_order_gables';

    protected $fillable = ['pay_order_gable_type', 'pay_order_gable_id', 'pay_order_id'];
}