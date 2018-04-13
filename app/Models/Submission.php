<?php namespace App\Models;
/**
 * @author: wanghui
 * @date: 2017/8/8 上午11:12
 * @email: wanghui@yonglibao.com
 */

use App\Models\Feed\Feed;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Submission
 *
 * @package App\Models
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
 * @property-read \App\Models\User $owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Readhub\Submission bookmarkedBy($user_id)
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Readhub\Submission withoutTrashed()
 * @property int $collections
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereCollections($value)
 * @property int $author_id
 * @property-read \App\Models\User $author
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereAuthorId($value)
 * @property int $views
 * @property int $group_id
 * @property int $is_recommend
 * @property int $public
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereIsRecommend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Submission whereViews($value)
 */
class Submission extends Model {

    use SoftDeletes,MorphManyCommentsTrait,MorphManyTagsTrait,BelongsToUserTrait;

    protected $table = 'submissions';

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
        'data', 'title', 'slug','author_id', 'type', 'category_id', 'category_name', 'rate',
        'upvotes', 'downvotes', 'user_id', 'views', 'data', 'approved_at','public',
        'deleted_at', 'comments_number'
    ];

    const RECOMMEND_STATUS_NOTHING = 0;
    const RECOMMEND_STATUS_PENDING = 1;
    const RECOMMEND_STATUS_PUBLISH = 2;


    public static function boot()
    {
        parent::boot();
        static::deleted(function($submission){
            Feed::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')
                ->delete();
            Collection::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')
                ->delete();
            Comment::where('source_id',$submission->id)
                ->where('source_type','App\Models\Submission')
                ->delete();
            Support::where('supportable_id',$submission->id)
                ->where('supportable_type','App\Models\Submission')
                ->delete();
            /*删除标签关联*/
            Taggable::where('taggable_type','=',get_class($submission))->where('taggable_id','=',$submission->id)->delete();
            /*删除动态*/
            Doing::where('source_type','=',get_class($submission))->where('source_id','=',$submission->id)->delete();
        });
    }

    public static function search($word)
    {
        $list = self::where('title','like',"%$word%");
        return $list;
    }

    public function formatTitle(){
        return strip_tags($this->title);
    }

    public function partHtmlTitle(){
        return strip_tags($this->title,'<a><span>');
    }

    /**
     * A submission is owned by a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->select(['id', 'name', 'avatar', 'uuid','is_expert']);
    }

    public function author(){
        return $this->belongsTo(User::class, 'author_id')
            ->select(['id', 'name', 'avatar', 'uuid','is_expert']);
    }

    public function group() {
        return $this->belongsTo('App\Models\Groups\Group');
    }

}