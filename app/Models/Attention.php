<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Attention
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUserId($value)
 * @mixin \Eloquent
 */
class Attention extends Model
{
    protected $table = 'attentions';
    protected $fillable = ['user_id','source_id','source_type'];


    public static function store(){

    }



}
