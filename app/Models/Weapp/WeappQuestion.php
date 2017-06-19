<?php namespace App\Models\WeappQuestion;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午6:49
 * @email: wanghui@yonglibao.com
 */
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

/**
 * @mixin \Eloquent
 */
class WeappQuestion extends Model implements HasMedia
{
    use BelongsToUserTrait, MorphManyCommentsTrait, HasMediaTrait;
    protected $table = 'weapp_questions';
    protected $fillable = ['title', 'user_id', 'description', 'is_public', 'status'];


}