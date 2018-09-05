<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: hank.huiwang@gmail.com
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadHubUser
 *
 * @package App\Models\Readhub
 * @mixin \Eloquent
 * @property int $user_id
 * @property int $category_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Subscription whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Subscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Subscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Subscription whereUserId($value)
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