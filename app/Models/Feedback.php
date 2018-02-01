<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Feedback
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $to_user_id
 * @property int $source_id
 * @property string $source_type
 * @property int $star
 * @property string|null $content
 * @property string $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereStar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereToUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Feedback whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 */
class Feedback extends Model
{
    use BelongsToUserTrait;
    protected $table = 'feedback';
    protected $fillable = ['user_id', 'source_type','source_id','content','to_user_id','star','created_at'];
    public $timestamps = false;

    public function source()
    {
        return $this->morphTo();
    }
}
