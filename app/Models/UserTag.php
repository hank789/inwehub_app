<?php

namespace App\Models;

use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\UserTag
 *
 * @property int $id
 * @property int $user_id
 * @property int $tag_id
 * @property int $questions
 * @property int $articles
 * @property int $answers
 * @property int $supports
 * @property int $adoptions
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereAdoptions($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereArticles($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereQuestions($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereSupports($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereTagId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\UserTag whereUserId($value)
 * @mixin \Eloquent
 * @property int $skills
 * @property int $industries
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTag whereIndustries($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\UserTag whereSkills($value)
 * @property-read \App\Models\Tag $tag
 */
class UserTag extends Model
{
    use BelongsToUserTrait;
    protected $table = 'user_tags';
    protected $fillable = ['user_id', 'tag_id','questions','articles','answers','supports','views'];
    

    /*用户标签统计*/
    public static function multiIncrement($user_id,$tags,$field=false){
        if(!$tags){
            return false;
        }
        foreach( $tags as $tag ){
            $userTag = self::firstOrCreate([
                'user_id'=> $user_id,
                'tag_id' => $tag->id
            ]);

            if($field){
                $userTag->increment($field);
            }
        }
    }

    public static function multiDecrement($user_id,$tags,$field){
        if(!$tags){
            return false;
        }
        foreach( $tags as $tag ){
            $userTag = self::where('user_id',$user_id)->where('tag_id',$tag->id)->first();
            if($userTag && $userTag->$field > 0){
                $userTag->decrement($field);
            }
        }
    }



    public function tag(){
        return $this->belongsTo('App\Models\Tag');
    }

    public static function multiDetachByField($user_id,$tags,$field){
        if(!$tags){
            return false;
        }
        foreach( $tags as $tag ){
            $userTag = self::where('user_id',$user_id)->where('tag_id',$tag->id)->first();

            if($userTag){
                $userTag->$field = 0;
                $userTag->save();
            }
        }
    }

    public static function detachByField($user_id,$field){
        self::where('user_id',$user_id)->where($field,'>',0)->update([$field=>0]);
    }




    /*初始化统计用户标签数据*/
    public static function figures(){
        $tags = Tag::all();
        $users = User::where('status','>',0)->get();
        $users->map(function($user) use($tags) {
            $tags->map(function($tag) use($user){
                $articleNum = $tag->articles()->where("user_id","=",$user->id)->count();
                $questions = $tag->questions()->where('status','>',0)->get();
                $questionNum = $answerNum = $supportNum = $adoptionNum = 0;
                foreach($questions as $question){
                    $questionNum++;
                    $answer = $question->answers()->where('user_id','=',$user->id)->first();
                    if($answer){
                        $answerNum++;
                        $supportNum += $answer->supports;
                        if( $answer->adopted_at ){
                            $adoptionNum++;
                        }
                    }
                }
                self::updateOrCreate([
                    'user_id'=>$user->id,
                    'tag_id'=>$tag->id
                ],
                [
                    'user_id'  => $user->id,
                    'tag_id'   => $tag->id,
                    'questions'=> $questionNum,
                    'articles' => $articleNum,
                    'answers'  => $answerNum,
                    'supports' => $supportNum,
                    'adoptions'=> $adoptionNum
                ]);
            });
        });

    }

}
