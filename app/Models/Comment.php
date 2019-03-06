<?php

namespace App\Models;

use App\Models\Feed\Feed;
use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property string $htmlContent
 * @property int $source_id
 * @property string $source_type
 * @property int $to_user_id
 * @property bool $status
 * @property int $supports
 * @property bool $device
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $source
 * @property-read \App\Models\User $toUser
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereDevice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereSupports($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereToUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Comment whereUserId($value)
 * @mixin \Eloquent
 * @property int $parent_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $children
 * @property-read \App\Models\User $owner
 * @property-read \App\Models\Comment $parent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereParentId($value)
 * @property int $level
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereLevel($value)
 * @property array $mentions
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Comment whereMentions($value)
 */
class Comment extends Model
{
    use BelongsToUserTrait;
    protected $table = 'comments';
    protected $fillable = ['user_id','level','parent_id', 'content','source_id','source_type','mentions','to_user_id','supports','status','comment_type'];

    protected $with = [
        'owner', 'children',
    ];

    protected $casts = [
        'mentions' => 'json',
    ];

    const COMMENT_TYPE_NORMAL = 0;
    const COMMENT_TYPE_OFFICIAL = 1;


    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->select(['id', 'name', 'avatar', 'uuid', 'is_expert']);
    }

    /*public function getContentAttribute($value)
    {
        return strip_tags($value);
    }*/

    public function formatContent(){
        return strip_tags($this->content);
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('withHtml', function(Builder $builder) {
            $builder->select('*','content as htmlContent');
        });

        /*监听创建*/
        static::creating(function($comment){
            /*开启状态检查*/
            if(Setting()->get('verify_comment')==1){
                $comment->status = 0;
            }
        });

        /*监听删除事件*/
        static::deleting(function($comment){
            /*问题、回答、文章评论数 -1*/
            if ($comment->source_type == 'App\Models\Submission') {
                $comment->source()->where("comments_number",">",0)->decrement('comments_number');
            } else {
                $comment->source()->where("comments",">",0)->decrement('comments');
            }
            Feed::where('source_id',$comment->id)->where('source_type',get_class($comment))->delete();
        });
    }

    public function source()
    {
        return $this->morphTo();
    }


    public function toUser(){
        return $this->belongsTo('App\Models\User','to_user_id');
    }

    /**
     * A comment has many children.
     *
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('created_at','asc');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function getSupportRateDesc() {
        return $this->supports;
    }

    public function getSupportPercent() {
        return $this->supports;
    }

}
