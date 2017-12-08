<?php

namespace App\Models;

use App\Logic\QuillLogic;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyFeedbackTrait;
use App\Models\Relations\MorphManyOrdersTrait;
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
 * @property int $pay_for_views
 * @property int $views
 * @property int $collections
 * @property string|null $promise_time 承诺响应时间
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Feedback[] $feedbacks
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pay\Order[] $orders
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereCollections($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer wherePayForViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer wherePromiseTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Answer whereViews($value)
 */
class Answer extends Model
{
    use MorphManyCommentsTrait,BelongsToUserTrait,MorphManyTagsTrait,MorphManyFeedbackTrait,MorphManyOrdersTrait;
    protected $table = 'answers';
    protected $fillable = ['question_title','question_id','user_id','adopted_at', 'content','status','promise_time', 'device'];

    const ANSWER_STATUS_FINISH = 1;
    const ANSWER_STATUS_REJECT = 2;
    const ANSWER_STATUS_PROMISE = 3;


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

    public function getContentText(){
        return QuillLogic::parseText($this->content);
    }

    public function getContentHtml(){
        return QuillLogic::parseHtml($this->content);
    }

    //回答好评率
    public function getFeedbackRate(){
        return 0;
        $count = $this->feedbacks()->count();
        $rate = $this->feedbacks()->sum('star');
        if ($count) {
            return (bcdiv($rate,$count * 5,2) * 100).'%';
        }
        return 0;
    }

}
