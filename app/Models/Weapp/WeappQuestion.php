<?php namespace App\Models\Weapp;
/**
 * App\Models\Weapp\WeappQuestion
 *
 * @author : wanghui
 * @date : 2017/6/16 下午6:49
 * @email : wanghui@yonglibao.com
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property int $price
 * @property int $is_public
 * @property int $answers
 * @property int $views
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property int $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Media[] $media
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereAnswers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereIsPublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Weapp\WeappQuestion whereViews($value)
 * @mixin \Eloquent
 */
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;


class WeappQuestion extends Model implements HasMedia
{
    use BelongsToUserTrait, MorphManyCommentsTrait, HasMediaTrait;
    protected $table = 'weapp_questions';
    protected $fillable = ['title', 'user_id', 'description', 'is_public', 'status'];


}