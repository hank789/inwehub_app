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
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCommentsNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereDownvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereNsfw($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereRecommendSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereRecommendStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereResubmitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUpvotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission whereUserId($value)
 */
class SubmissionDownvotes extends Model {

    protected $table = 'submission_downvotes';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';


}