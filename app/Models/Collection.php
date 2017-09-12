<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Collection
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property string $subject
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Collection whereUserId($value)
 * @mixin \Eloquent
 */
class Collection extends Model
{
    use BelongsToUserTrait;

    protected $table = 'collections';
    protected $fillable = ['user_id','source_id','source_type','subject'];

}
