<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\UserData
 *
 * @property int $user_id
 * @property int $coins
 * @property int $credits
 * @property string $registered_at
 * @property string $last_visit
 * @property string $last_login_ip
 * @property int $questions
 * @property int $articles
 * @property int $answers
 * @property int $adoptions
 * @property int $supports
 * @property int $followers
 * @property int $views
 * @property bool $email_status
 * @property bool $mobile_status
 * @property bool $authentication_status
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereAdoptions($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereArticles($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereAuthenticationStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereCoins($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereCredits($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereEmailStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereFollowers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereLastLoginIp($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereLastVisit($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereMobileStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereQuestions($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereRegisteredAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereSupports($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserData whereViews($value)
 * @mixin \Eloquent
 * @property int $user_level
 * @property int $is_company
 * @property int $edu_public
 * @property int $project_public
 * @property int $job_public
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereEduPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereIsCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereJobPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereProjectPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereUserLevel($value)
 * @property string $geohash
 * @property string $latitude 纬度
 * @property string $longitude 经度
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereGeohash($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserData whereLongitude($value)
 */
class UserData extends Model
{
    use BelongsToUserTrait;
    protected $table = 'user_data';

    public $timestamps = false;
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'coins','credits','authentication_status','last_login_ip', 'phone_public', 'registered_at','last_visit','geohash','longitude','latitude'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /*文章活跃用户*/
    public static function activeInArticles($size=8)
    {

        $list = Cache::remember('active_in_articles',10,function() use($size) {
            return  self::leftJoin('users', 'users.id', '=', 'user_data.user_id')
                          ->where('users.status','>',0)->where('user_data.articles','>',0)
                          ->orderBy('user_data.articles','DESC')
                          ->orderBy('users.created_at','DESC')
                          ->select('users.id','users.name','users.title','user_data.coins','user_data.credits','user_data.followers','user_data.supports','user_data.answers','user_data.articles','user_data.authentication_status')
                          ->take($size)->get();
        });

        return  $list;
    }



    /*活跃用户*/
    public static function activities($size)
    {
        return  self::leftJoin('users', 'users.id', '=', 'user_data.user_id')
            ->where('users.status','>',0)
            ->orderBy('user_data.answers','DESC')
            ->orderBy('user_data.articles','DESC')
            ->orderBy('users.updated_at','DESC')
            ->select('users.id','users.name','users.title','user_data.coins','user_data.credits','user_data.followers','user_data.supports','user_data.answers','user_data.articles','user_data.authentication_status')
            ->take($size)->get();
    }



    /*财富榜*/

    public static function topCoins($size)
    {
        return  self::leftJoin('users', 'users.id', '=', 'user_data.user_id')
            ->where('users.status','>',0)->where('user_data.articles','>',0)
            ->orderBy('user_data.coins','DESC')
            ->select('users.id','users.name','users.title','user_data.coins','user_data.credits','user_data.followers','user_data.supports','user_data.answers','user_data.articles','user_data.authentication_status')
            ->take($size)->get();
    }


    /*排行榜*/
    public static function  top($type,$size)
    {
        return  self::leftJoin('users', 'users.id', '=', 'user_data.user_id')
            ->where('users.status','>',0)
            ->orderBy('user_data.'.$type,'DESC')
            ->orderBy('user_data.last_visit','DESC')
            ->select('users.id','users.name','users.title','user_data.coins','user_data.credits','user_data.followers','user_data.supports','user_data.answers','user_data.articles','user_data.authentication_status')
            ->take($size)->get();
    }


    /*用户采纳率*/
    public function adoptPercent()
    {
        return round($this->adoptions / $this->answers, 2) * 100;
    }











}
