<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Class ReadHubUser
 *
 * @package App\Models\Readhub
 * @mixin \Eloquent
 * @property int $id
 * @property int $submission_id
 * @property int $user_id
 * @property int $parent_id
 * @property int $category_id
 * @property int $level
 * @property float $rate
 * @property int $upvotes
 * @property int $downvotes
 * @property string $body
 * @property string|null $approved_at
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $edited_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereDownvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereEditedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereSubmissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereUpvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Comment whereUserId($value)
 */
class Comment extends Model {

    protected $table = 'comments';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';


}