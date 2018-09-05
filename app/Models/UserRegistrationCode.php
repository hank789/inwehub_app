<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\UserRegistrationCode
 *
 * @author : wanghui
 * @date : 2017/5/22 下午3:50
 * @email : hank.huiwang@gmail.com
 * @mixin \Eloquent
 * @property int $id
 * @property int $recommend_uid 邀请人uid
 * @property int $register_uid 注册者uid
 * @property string|null $keyword 邀请对象关键词
 * @property string $code
 * @property int $status 状态:0未生效,1已生效,2已使用
 * @property string|null $expired_at 过期时间
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereRecommendUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereRegisterUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserRegistrationCode whereUpdatedAt($value)
 */

class UserRegistrationCode extends Model {

    protected $table = 'user_registration_code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','recommend_uid', 'keyword','code','status','expired_at'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    const CODE_STATUS_DRAFT = 0;
    const CODE_STATUS_PENDING = 1;
    const CODE_STATUS_USED = 2;
    const CODE_STATUS_EXPIRED = 3;


    public static function genCode(){
        $code = strtolower(Str::random(6));
        while(self::where('code',$code)->first()){
            $code = strtolower(Str::random(6));
        }
        return $code;
    }

    public function getRecommendUser(){
        return User::find($this->recommend_uid);
    }

    public function getRegisterUser(){
        $user = User::find($this->register_uid);
        if(!$user){
            $user = new \stdClass();
            $user->name = '';
        }
        return $user;
    }

}