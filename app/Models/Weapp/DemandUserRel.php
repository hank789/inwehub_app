<?php namespace App\Models\Weapp;
use Illuminate\Database\Eloquent\Model;


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
    protected $table = 'demand_user_rel';
    protected $fillable = ['demand_id', 'user_oauth_id','subscribes'];

    protected $casts = [
        'subscribes' => 'json'
    ];

    public function demand()
    {
        return $this->belongsTo('App\Models\Weapp\Demand');
    }

    public function userOauth() {
        return $this->belongsTo('App\Models\UserOauth');
    }

    public function formatSubscribes() {
        $items = [
            '未来有此类似的工作，请通知我',
            '未来有此公司的新职位，请通知我',
            '未来此发布者有新招募，请通知我'
        ];
        $subscribe = '';
        foreach ($this->subscribes as $index) {
            $subscribe = $subscribe .','. $items[$index];
        }
        return trim($subscribe,',');
    }
}