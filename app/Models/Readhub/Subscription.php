<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadHubUser
 * @package App\Models\Readhub
 * @mixin \Eloquent
 */
class Subscription extends Model {

    protected $table = 'subscriptions';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    protected $fillable = [
        'user_id','category_id'
    ];

}