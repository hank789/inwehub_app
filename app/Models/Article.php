<?php

namespace App\Models;

use App\Models\Relations\BelongsToCategoryTrait;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyCommentsTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * App\Models\Article
 *
 * @property int $id
 * @property int $user_id
 * @property string $logo
 * @property int $category_id
 * @property string $title
 * @property string $summary
 * @property string $content
 * @property int $views
 * @property int $collections
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property int $supports
 * @property bool $status
 * @property bool $device
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereCollections($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereComments($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereContent($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereDevice($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereLogo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereSummary($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereSupports($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Article whereViews($value)
 * @mixin \Eloquent
 * @property string|null $deadline
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Article whereDeadline($value)
 */
class Article extends Model
{
    use BelongsToUserTrait,MorphManyTagsTrait,MorphManyCommentsTrait,BelongsToCategoryTrait;
    protected $table = 'articles';
    protected $fillable = ['title', 'user_id','category_id', 'content','tags','summary','status','logo'];

    const ARTICLE_STATUS_PENDING = 0;//待审核
    const ARTICLE_STATUS_ONLINE = 1;//审核成功
    const ARTICLE_STATUS_CLOSED = 2;//已结束


    public static function boot()
    {
        parent::boot();

        /*监听创建*/
        static::creating(function($article){
            /*开启状态检查*/
            if(Setting()->get('verify_article')==1){
                $article->status = 0;
            }
            if( trim($article->summary) === '' ){
                $article->summary = str_limit(strip_tags($article->content),180);
            }

        });

        static::saved(function($article){

            if(Setting()->get('xunsearch_open',0) == 1){
                App::offsetGet('search')->update($article);
            }
        });
        /*监听删除事件*/
        static::deleting(function($article){

            /*用户文章数 -1 */
            $article->user->userData()->where("articles",">",0)->decrement('articles');

            Collection::where('source_type','=',get_class($article))->where('source_id','=',$article->id)->delete();

            /*删除回答评论*/
            Comment::where('source_type','=',get_class($article))->where('source_id','=',$article->id)->delete();
            /*删除动态*/
            Doing::where('source_type','=',get_class($article))->where('source_id','=',$article->id)->delete();


        });

        static::deleted(function($article){
            if(Setting()->get('xunsearch_open',0) == 1){
                App::offsetGet('search')->delete($article);
            }
        });
    }

    /*获取相关文章*/
    public static function correlations($tagIds,$size=6)
    {
        $questions = self::whereHas('tags', function($query) use ($tagIds) {
            $query->whereIn('tag_id', $tagIds);
        })->orderBy('created_at','DESC')->take($size)->get();
        return $questions;
    }


    /*搜索*/
    public static function search($word)
    {
        $list = self::where('title','like',"$word%");
        return $list;
    }


    /*推荐文章*/
    public static function recommended($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }

        $list = $query->where('status','>',0)->orderBy('supports','DESC')->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }

    /*热门文章*/
    public static function hottest($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->orderBy('views','DESC')->orderBy('collections','DESC')->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;

    }


    /*最新问题*/
    public static function newest($categoryId=0 , $pageSize=20)
    {
        $query = self::query();
        if( $categoryId > 0 ){
            $query->where('category_id','=',$categoryId);
        }
        $list = $query->where('status','>',0)->orderBy('created_at','DESC')->paginate($pageSize);
        return $list;
    }




}
