<?php namespace App\Models\Readhub;
use Illuminate\Database\Eloquent\Model;

/**
 * @author: wanghui
 * @date: 2017/8/1 上午10:46
 * @email: wanghui@yonglibao.com
 */

/**
 * Class ReadHubUser
 * @package App\Models\Readhub
 * @mixin \Eloquent
 */
class ReadHubUser extends Model {

    protected $table = 'users';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    protected $fillable = [
        'username', 'name', 'email', 'password', 'location', 'bio',
        'website', 'settings', 'color', 'avatar', 'confirmed',
        'active', 'info', 'comment_karma', 'submission_karma',
    ];

    protected $casts = [
        'settings' => 'json',
        'info'     => 'json',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at', 'email', 'settings', 'verified', 'active',
    ];
}