<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int $user_id
 * @property string $content
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
 */
class Comment extends Model
{
    use BelongsToUserTrait;
    protected $table = 'comments';
    protected $fillable = ['user_id','level','parent_id', 'content','source_id','source_type','to_user_id','supports','status'];


    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id')
            ->select(['id', 'name', 'avatar', 'uuid', 'is_expert']);
    }

    public static function boot()
    {
        parent::boot();

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
            $comment->source()->where("comments",">",0)->decrement('comments');
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

}
