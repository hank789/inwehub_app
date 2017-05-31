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
 * @property int $province
 * @property string $title
 * @property string $description
 * @property bool $gender
 * @property int $city
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserTag[] $userTags
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereCity($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereFailedReason($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereGender($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereIdCard($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereIdCardImage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereProvince($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereRealName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereSkill($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereSkillImage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Authentication whereUserId($value)
 * @mixin \Eloquent
 */
class Authentication extends Model
{
    use BelongsToUserTrait,BelongsToCategoryTrait;
    protected $table = 'authentications';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id','status','category_id','failed_reason'];

    public static function boot()
    {
        parent::boot();

        static::creating(function($authentication){
            $authentication->userData->update(['authentication_status'=>$authentication->status]);
        });

        static::updating(function($authentication){
            $authentication->userData->update(['authentication_status'=>$authentication->status]);
        });
    }

    public function userData()
    {
        return $this->belongsTo('App\Models\UserData','user_id','user_id');
    }

    /*用户统计标签*/
    public function userTags(){
        return $this->hasMany('App\Models\UserTag','user_id','user_id');
    }

    public function hotTags(){
        $hotTagIds = $this->userTags()->select("tag_id")->distinct()->orderBy('supports','desc')->orderBy('answers','desc')->orderBy('created_at','desc')->take(5)->pluck('tag_id');
        $tags = [];
        foreach($hotTagIds as $hotTagId){
            $tag = Tag::find($hotTagId);
            if($tag){
                $tags[] = $tag;
            }

        }
        return $tags;
    }

    public function getLevelName(){
        return "认证专家";
    }

    /*推荐行家*/
    public static function hottest($size)
    {
        return  self::leftJoin('user_data', 'user_data.user_id', '=', 'authentications.user_id')
            ->where('user_data.authentication_status','=',1)
            ->orderBy('user_data.answers','DESC')
            ->orderBy('user_data.articles','DESC')
            ->orderBy('authentications.updated_at','DESC')
            ->select('authentications.user_id','authentications.real_name','authentications.title','user_data.coins','user_data.credits','user_data.followers','user_data.supports','user_data.answers','user_data.articles','user_data.authentication_status')
            ->take($size)->get();
    }

}
