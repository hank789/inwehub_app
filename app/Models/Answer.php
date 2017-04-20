<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\MorphManyTagsTrait;

/**
 * App\Models\Answer
 *
 * @property int $id
 * @property string $question_title
 * @property int $question_id
 * @property int $user_id
 * @property string $content
 * @property int $supports
 * @property int $oppositions
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property bool $device
 * @property bool $status
 * @property string $adopted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Question $question
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereAdoptedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereComments($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereDevice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereOppositions($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereQuestionId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereQuestionTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereSupports($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Answer whereUserId($value)
 * @mixin \Eloquent
 */
class Answer extends Model
{
    use MorphManyCommentsTrait,BelongsToUserTrait,MorphManyTagsTrait;
    protected $table = 'answers';
    protected $fillable = ['question_title','question_id','user_id','adopted_at', 'content','status','promise_time'];

    public static function boot()
    {
        parent::boot();

        /*监听创建*/
        static::creating(function($answer){
            /*开启状态检查*/
            if(Setting()->get('verify_answer')==1){
                $answer->status = 0;
            }

        });
        /*监听删除事件*/
        static::deleting(function($answer){

            /*问题回答数 -1 */
            $answer->question()->where('answers','>',0)->decrement('answers');

            /*用户回答数 -1 */
            $answer->user->userData()->where('answers','>',0)->decrement('answers');

            /*删除动态*/
            Doing::where('source_type','=',get_class($answer))->where('source_id','=',$answer->id)->delete();

            /*删除回答评论*/
            Comment::where('source_type','=',get_class($answer))->where('source_id','=',$answer->id)->delete();

        });
    }



    public function question(){
        return $this->belongsTo('App\Models\Question');
    }
}
