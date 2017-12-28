<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Doing
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property int $source_id
 * @property string $source_type
 * @property string $subject
 * @property string $content
 * @property int $refer_id
 * @property int $refer_user_id
 * @property string $refer_content
 * @property string $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereReferContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereReferId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereReferUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereSourceType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Doing whereUserId($value)
 * @mixin \Eloquent
 * @property int $is_hide
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Doing whereIsHide($value)
 */
class Doing extends Model
{
    use BelongsToUserTrait;
    protected $table = 'doings';
    protected $fillable = ['user_id', 'action','source_type','source_id','subject','content','refer_id','refer_user_id','refer_content','created_at'];
    public $timestamps = false;

    const ACTION_VIEW_RESUME = 'view_resume';
    const ACTION_VIEW_PAY_QUESTION = 'view_pay_question';
    const ACTION_VIEW_FREE_QUESTION = 'view_free_question';
    const ACTION_VIEW_ANSWER = 'view_answer';
    const ACTION_PAY_FOR_VIEW_ANSWER = 'pay_for_view_answer';
    const ACTION_VIEW_SUBMISSION = 'view_submission';
    const ACTION_SHARE_QUESTION_SUCCESS = 'share_question_success';
    const ACTION_SHARE_ANSWER_SUCCESS = 'share_answer_success';
    const ACTION_SHARE_INVITE_ANSWER_SUCCESS = 'share_invite_answer_success';
    const ACTION_SHARE_RESUME_SUCCESS = 'share_resume_success';
    const ACTION_SHARE_SUBMISSION_SUCCESS = 'share_submission_success';
    const ACTION_SHARE_INVITE_REGISTER_SUCCESS = 'share_invite_register_success';



    public function source()
    {
        return $this->morphTo();
    }

    static function correlation(User $user)
    {
      $attentions = $user->attentions()->get();
      $tags = $questions = $users = [];

      foreach($attentions as $attention){
          if($attention->source_type == 'App\Models\Tag'){
                $tags[] = $attention->source_id;
          }elseif($attention->source_type == 'App\Models\User'){
                $users[] = $attention->source_id;
          }elseif($attention->source_type == 'App\Models\Question'){
                $questions[] = $attention->source_id;
          }
      }

        /*追加用户标签*/
      foreach( $user->tags()->get() as $tag ){
          $tags[] = $tag->id;
      }

      if($tags){
            $taggables = DB::table("taggables")->whereIn("tag_id",$tags)->get();
            foreach($taggables as $tagable){
                if($tagable->taggable_type == 'App\Models\Question'){
                    $questions[] = $tagable->taggable_id;
                }
            }
      }

      return self::where(function($query) use($users){
                     $query->whereIn("user_id",$users);
                 })
                 ->oRwhere(function($query) use($questions){
                     $query->whereIn("source_id",$questions)->where("source_type","=","App\Models\Question");

                 })
                 ->where('doings.user_id','<>',$user->id)
             //->where('attentions.created_at','<','doings.created_at')
             ->select('doings.*')
             ->orderBy('doings.created_at','DESC');
    }
}
