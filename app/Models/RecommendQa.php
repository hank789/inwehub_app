<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Recommendation
 *
 * @property int $id
 * @mixin \Eloquent
 * @property string $subject
 * @property string $user_name
 * @property string $user_avatar_url
 * @property int $price
 * @property int $type
 * @property int $sort
 * @property int $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereUserAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\RecommendQa whereUserName($value)
 */
class RecommendQa extends Model
{
    protected $table = 'operate_recommend_qa';
    protected $fillable = ['subject','user_name','user_avatar_url','type','price','sort','status'];

}
