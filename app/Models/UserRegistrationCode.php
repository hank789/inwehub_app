<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @author: wanghui
 * @date: 2017/5/22 下午3:50
 * @email: wanghui@yonglibao.com
 *
 * @mixin \Eloquent
 */

class UserRegistrationCode extends Model {

    protected $table = 'user_registration_code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','recommend_uid', 'mobile','code','status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    const CODE_STATUS_DRAFT = 0;
    const CODE_STATUS_PENDING = 1;
    const CODE_STATUS_USED = 2;


    public static function genCode(){
        $code = Str::random(6);
        while(self::where('code',$code)->first()){
            $code = Str::random(6);
        }
        return $code;
    }

}