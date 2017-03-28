<?php

namespace App\Models;

use App\Models\Relations\BelongsToCategoryTrait;
use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Authentication
 *
 * @property int $user_id
 * @property int $category_id
 * @property string $real_name
 * @property string $id_card
 * @property string $id_card_image
 * @property string $skill
 * @property string $skill_image
 * @property string $failed_reason
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\User $user
 * @property-read \App\Models\UserData $userData
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereFailedReason($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereIdCard($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereIdCardImage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereRealName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereSkill($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereSkillImage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereUserId($value)
 * @mixin \Eloquent
 */
class Authentication extends Model
{
    use BelongsToUserTrait,BelongsToCategoryTrait;
    protected $table = 'authentications';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id','real_name','id_card','id_card_image','skill','skill_image','status','category_id'];

    public static function boot()
    {
        parent::boot();

        static::updating(function($authentication){
            $authentication->userData->update(['authentication_status'=>$authentication->status]);
        });
    }

    public function userData()
    {
        return $this->belongsTo('App\Models\UserData','user_id','user_id');
    }

}
