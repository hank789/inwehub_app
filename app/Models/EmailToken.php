<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * App\Models\EmailToken
 *
 * @property int $id
 * @property string $email
 * @property string $action
 * @property string $token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\EmailToken whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\EmailToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\EmailToken whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\EmailToken whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\EmailToken whereToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\EmailToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EmailToken extends Model
{
    protected $table = 'email_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email','action','token'];


    /*清空toke信息*/
    public static function clear($email,$action)
    {
        self::where('email','=',$email)->where('action','=',$action)->delete();
    }



    public static function createToken()
    {
        return hash_hmac('sha256',Str::random(40),Config::get('key'));
    }


}
