<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Exchange
 *
 * @property int $id
 * @property int $user_id
 * @property int $goods_id
 * @property int $coins
 * @property string $real_name
 * @property string $phone
 * @property string $email
 * @property string $comment
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Goods $goods
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereCoins($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereComment($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereGoodsId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange wherePhone($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereRealName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Exchange whereUserId($value)
 * @mixin \Eloquent
 */
class Exchange extends Model
{
    use BelongsToUserTrait;
    protected $table = 'exchanges';
    protected $fillable = ['user_id', 'goods_id','real_name','phone','email','comment','status'];


    static function newest()
    {
        return self::orderBy('created_at','desc')->take(10)->get();
    }


    public function goods(){
        return $this->belongsTo('App\Models\Goods','goods_id');
    }


}
