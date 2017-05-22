<?php

namespace App\Models;

use App\Models\Relations\BelongsToCategoryTrait;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyDoingsTrait;
use App\Models\Relations\MorphManyOrdersTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Question
 *
 * @property int $id
 * @property int $user_id
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property int $price
 * @property bool $hide
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Answer[] $answers
 * @property int $views
 * @property int $followers
 * @property int $collections
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property bool $device
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionInvitation[] $invitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereAnswers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCollections($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereComments($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereDevice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereFollowers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereHide($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question wherePrice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Question whereViews($value)
 * @mixin \Eloquent
 */
class Question extends Model
{
    use BelongsToUserTrait,MorphManyCommentsTrait,MorphManyTagsTrait,BelongsToCategoryTrait, MorphManyDoingsTrait, MorphManyOrdersTrait;
    protected $table = 'questions';
    protected $fillable = ['title', 'user_id','category_id','description','tags','price','hide','status'];


    public static function boot()
    {
        parent::boot();

        /*监听问题创建*/
        static::creating(function($question){
            /*开启问题审核状态检查*/
            if(Setting()->get('verify_question')==1){
                $question->status = 0;
            }


        });

        static::saved(function($question){
            if(Setting()->get('xunsearch_open',0) == 1) {
                App::offsetGet('search')->update($question);
            }
        });


        static::deleted(function($question){

            /*删除回答数据*/
            Answer::where("question_id","=",$question->id)->delete();

            UserData::where("user_id","=",$question->user_id)->where("questions",">",0)->decrement('questions');
            /*删除问题评论*/
            Comment::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            /*删除动态*/
            Doing::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            /*删除问题关注*/
            Attention::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            /*删除标签关联*/
            Taggable::where('taggable_type','=',get_class($question))->where('taggable_id','=',$question->id)->delete();

            /*删除问题邀请*/
            QuestionInvitation::where('question_id','=',$question->id)->delete();

            /*删除问题收藏*/
            Collection::where('source_type','=',get_class($question))->where('source_id','=',$question->id)->delete();

            if(Setting()->get('xunsearch_open',0) == 1) {
                App::offsetGet('search')->delete($question);
            }

        });
    }

    public function statusHumanDescription($user_id){
        $description = '';
        switch ($this->status){
            case 0:
                $description = '未发布';
                break;
            case 1:
                $description = '您的提问平台已经受理,我们将会尽快为您寻找合适的专家!';
                break;
            case 2:
                $description = '您的问题已分配给专家,正在等待专家响应';
                break;
            case 3:
                $description = '问题已关闭';
                break;
            case 4:
                $answer = $this->answers()->whereIn('status',[1,3])->orderBy('id','asc')->first();
                $desc = promise_time_format($answer->promise_time);
                if($user_id == $this->user_id){
                    $description = $answer->user->name.'已承诺,'.$desc['desc'];
                }else{
                    $description = '倒计时'.$desc['diff'];
                }
                break;
            case 5:
                $description = '您的提问平台已经受理,我们将会尽快为您寻找合适的专家!';
                break;
            case 6:
                $answer = $this->answers()->orderBy('id','desc')->first();
                $description = $answer->user->name.'回答了您的问题';
                break;
            case 7:
                $answer = $this->answers()->orderBy('id','desc')->first();
                $description = $answer->user->name.'回答了您的问题';
                break;
        }
        return $description;
    }

    public function formatTimeline(){
        $doings = $this->doings()->orderBy('id','asc')->get();
        $timeline = [];
        $is_find_expert = false;
        foreach($doings as $doing){
            $title = '';
            switch($doing->action){
                case 'question_submit':
                    $title = '问题成功提交';
                    break;
                case 'question_process':
                    $title = '平台已经受理,正在为您找寻合适专家';
                    break;
                case 'question_answer_confirming':
                    if($is_find_expert) continue;
                    $title = '平台已经帮您找到合适的专家,等待确认';
                    $is_find_expert = true;
                    break;
                case 'question_answer_confirmed':
                    $title = $doing->user->name.'为您回答问题';
                    break;
                case 'question_answered':
                    $title = $doing->user->name.'已为您回答问题';
                    break;
                case 'question_answer_rejected':
                    break;
            }
            if($title){
                $timeline[] = [
                    'title' => $title,
                    'created_at' => (string)$doing->created_at
                ];
            }
        }
        return $timeline;
    }

    //已邀请
    public function invitedAnswer(){
        //只有状态是待分配和已拒绝时才要更改状态
        if($this->status != 4 && $this->status != 6 && $this->status != 7){
            $this->status = 2;
            return $this->save();
        }
    }

    //已确认待回答
    public function confirmedAnswer(){
        if($this->status != 6 && $this->status != 7){
            $this->status = 4;
            return $this->save();
        }
    }

    //已拒绝
    public function rejectAnswer(){
        if($this->status != 4 && $this->status != 6 && $this->status != 7){
            $this->status = 5;
            return $this->save();
        }
    }

    //已回答
    public function answered()
    {
        if($this->status != 7){
            $this->status = 6;
            return $this->save();
        }
    }

    /*获取相关问题*/
    public static function correlations($tagIds,$size=6)
    {
        $questions = self::whereHas('tags', function($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        })->orderBy('created_at','DESC')->take($size)->get();
        return $questions;
    }




    /*热门问题*/
    public static function hottest($categoryId = 0,$pageSize=20)
    {
        $query = self::with('user');
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->orderBy('views','DESC')->orderBy('answers','DESC')->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;

    }

    /*最新问题*/
    public static function newest($categoryId=0 , $pageSize=20)
    {
        $query = self::with('user');
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*未回答的*/
    public static function unAnswered($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->where('answers','=',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*悬赏问题*/
    public static function reward($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->where('price','>',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*最近热门问题*/
    public static function recent()
    {
        $list = Cache::remember('recent_questions',300, function() {
            return self::where('status','>',0)->where('created_at','>',Carbon::today()->subWeek())->orderBy('views','DESC')->orderBy('answers','DESC')->orderBy('created_at','DESC')->take(12)->get();
        });

        return $list;
    }

    /*是否已经邀请用户回答了*/
    public function isInvited($sendTo,$fromUserId){
        return $this->invitations()->where("send_to","=",$sendTo)->where("from_user_id","=",$fromUserId)->count();
    }


    /*问题搜索*/
    public static function search($word,$size=16)
    {
        $list = self::where('title','like',"$word%")->paginate($size);
        return $list;
    }


    /*问题所有回答*/
    public function answers()
    {
        return $this->hasMany('App\Models\Answer','question_id');
    }


    /*问题所有邀请*/
    public function invitations()
    {
        return $this->hasMany('App\Models\QuestionInvitation','question_id');
    }


}
