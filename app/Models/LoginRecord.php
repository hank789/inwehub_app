<?php namespace App\Models;

/**
 * @author: wanghui
 * @date: 2017/4/7 下午6:49
 * @email: wanghui@yonglibao.com
 */

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

class LoginRecord extends Model
{
    use BelongsToUserTrait;

    protected $table = 'login_records';
    protected $fillable = ['user_id', 'ip','address','device_system','device_name','device_model','device_code'];

}