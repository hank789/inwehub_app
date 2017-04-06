<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;

/**
 * App\Models\Credit
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property int $source_id
 * @property string $source_subject
 * @property int $coins
 * @property int $credits
 * @property string $created_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereCoins($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereCredits($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereSourceSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereUserId($value)
 * @mixin \Eloquent
 */
class Credit extends Model
{
    use BelongsToUserTrait;
    protected $table = 'credits';
    protected $fillable = ['user_id', 'action','coins','credits','source_id','source_subject','created_at'];
    public $timestamps = false;
}
