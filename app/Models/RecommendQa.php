<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Recommendation
 *
 * @property int $id
 * @mixin \Eloquent
 */
class RecommendQa extends Model
{
    protected $table = 'operate_recommend_qa';
    protected $fillable = ['subject','user_name','user_avatar_url','type','price','sort','status'];

}
