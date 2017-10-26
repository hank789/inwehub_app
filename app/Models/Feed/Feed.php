<?php

namespace App\Models\Feed;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Attention
 *
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property \Carbon\Carbon $created_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Attention whereUserId($value)
 * @mixin \Eloquent
 */
class Feed extends Model
{
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'feeds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'user_id', 'feed_type','source_id','source_type','data','audit_status'
    ];

    protected $casts = [
        'data' => 'json',
    ];

    const FEED_TYPE_ANSWER_PAY_QUESTION = 1;//回答专业问题
    const FEED_TYPE_ANSWER_FREE_QUESTION = 2;//回答互动问题
    const FEED_TYPE_CREATE_FREE_QUESTION = 3;//发布互动问题
    const FEED_TYPE_CREATE_PAY_QUESTION = 4;//发布专业问题
    const FEED_TYPE_SUBMIT_ARTICLE = 5;//发布文章
    const FEED_TYPE_FOLLOW_FREE_QUESTION = 6;//关注了互动问答
    const FEED_TYPE_FOLLOW_USER = 7;//关注了用户
    const FEED_TYPE_COMMENT_PAY_QUESTION = 8;//评论了专业问答
    const FEED_TYPE_COMMENT_FREE_QUESTION = 9;//评论了互动问答
    const FEED_TYPE_COMMENT_ARTICLE = 10;//评论了文章
    const FEED_TYPE_UPVOTE_PAY_QUESTION = 11;//赞了专业问答
    const FEED_TYPE_UPVOTE_FREE_QUESTION = 12;//赞了互动问答
    const FEED_TYPE_UPVOTE_ARTICLE = 13;//赞了文章






    public function source(): MorphTo
    {
        return $this->morphTo();
    }

}
