<?php namespace App\Models\Weapp;
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;


/**
 * App\Models\Weapp\DemandUserRel
 *
 * @property int $id
 * @property int $user_id
 * @property int $demand_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\DemandUserRel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\DemandUserRel whereDemandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\DemandUserRel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\DemandUserRel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\DemandUserRel whereUserId($value)
 * @mixin \Eloquent
 * @property int $user_oauth_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\DemandUserRel whereUserOauthId($value)
 */
class DemandUserRel extends Model
{
    use BelongsToUserTrait;
    protected $table = 'demand_user_rel';
    protected $fillable = ['demand_id', 'user_oauth_id'];

}