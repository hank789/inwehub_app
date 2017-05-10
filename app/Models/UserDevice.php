<?php namespace App\Models;
use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/5/9 下午6:00
 * @email: wanghui@yonglibao.com
 * @mixin \Eloquent
 */
class UserDevice extends Model {
    use BelongsToUserTrait;

    protected $table = 'user_device';
    protected $fillable = ['client_id', 'user_id','device_token','appid','appkey','device_type'];
    public $timestamps = false;

}