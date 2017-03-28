<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Goods
 *
 * @property int $id
 * @property string $logo
 * @property string $name
 * @property string $description
 * @property int $coins
 * @property int $remnants
 * @property bool $post_type
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereCoins($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereLogo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods wherePostType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereRemnants($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Goods whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Goods extends Model
{
    protected $table = 'goods';
    protected $fillable = ['name', 'logo','post_type','description','coins','remnants','status'];





}
