<?php namespace App\Models\Readhub;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: wanghui@yonglibao.com
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ReadHubUser
 *
 * @package App\Models\Readhub
 * @mixin \Eloquent
 * @property int $id
 * @property int $recommend_status
 * @property int $recommend_sort
 * @property string $slug
 * @property string $title
 * @property string $type
 * @property array $data
 * @property string $category_name
 * @property float $rate
 * @property int|null $resubmit_id
 * @property int $user_id
 * @property int $nsfw
 * @property int $category_id
 * @property int $upvotes
 * @property int $downvotes
 * @property int $comments_number
 * @property string|null $approved_at
 * @property string|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $url
 * @property string|null $domain
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Readhub\Bookmark[] $bookmarks
 * @property-read \App\Models\Readhub\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Readhub\Comment[] $comments
 * @property-read \App\Models\Readhub\ReadHubUser $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission bookmarkedBy($user_id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission withoutTrashed()
 */
class Submission extends Model {

    use SoftDeletes,Bookmarkable;

    protected $table = 'submissions';

    /**
     * 此模型的连接名称。
     *
     * @var string
     */
    protected $connection = 'inwehub_read';

    protected $casts = [
        'data' => 'json'
    ];

    protected $with = ['owner'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'title', 'slug', 'type', 'category_id', 'category_name', 'rate',
        'upvotes', 'downvotes', 'user_id','recommend_status','recommend_sort', 'data', 'nsfw', 'approved_at',
        'deleted_at', 'comments_number','url', 'domain'
    ];

    const RECOMMEND_STATUS_NOTHING = 0;
    const RECOMMEND_STATUS_PENDING = 1;
    const RECOMMEND_STATUS_PUBLISH = 2;

    /**
     * A submission is owned by a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function owner()
    {
        return $this->belongsTo(ReadHubUser::class, 'user_id')
            ->select(['id', 'username', 'avatar', 'is_expert','uuid']);
    }

    /**
     * A Submission belongs to a Category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * A submission can have many comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * A helper to generate a valid URL to the submission.
     *
     * @return string
     */
    public function url()
    {
        return '/c/'.$this->category_name.'/'.$this->slug;
    }

}