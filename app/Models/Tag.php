<?php

namespace App\Models;

use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Relations\BelongsToCategoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * App\Models\Tag
 *
 * @property int $id
 * @property string $name
 * @property int $category_id
 * @property string $logo
 * @property string $summary
 * @property string $description
 * @property int $parent_id
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\UserData[] $followers
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserTag[] $userTags
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereFollowers($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereLogo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereSummary($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Tag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Tag extends Model
{
    use BelongsToCategoryTrait;
    protected $table = 'tags';
    protected $fillable = ['name', 'logo', 'description','category_id','followers'];


    public static function boot()
    {
        parent::boot();

        static::saved(function($tag){
            if(Setting()->get('xunsearch_open',0) == 1) {
                App::offsetGet('search')->update($tag);
            }
        });

        /*监听删除事件*/
        static::deleted(function($tag){
            /*删除关注*/
            Attention::where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->delete();
            $tag->userTags()->delete();
            /*删除用户标签*/
            UserTag::where('tag_id','=',$tag->id)->delete();
            Taggable::where('tag_id',$tag->id)->delete();
            if(Setting()->get('xunsearch_open',0) == 1){
                App::offsetGet('search')->delete($tag);
            }
        });
    }

    /**通过字符串添加标签
     * @param $tagString
     * @param $question_id
     */
    public static function multiSave($tagString,$taggable)
    {
        $tags = array_unique(explode(",",$tagString));

        /*删除所有标签关联*/
        if($tags){
            $taggable->tags()->detach();
        }

        foreach($tags as $tag_name){

            if(!trim($tag_name)){
                continue;
            }

            $tag = self::firstOrCreate(['name'=>$tag_name]);

            if(!$taggable->tags->contains($tag->id))
            {
                $taggable->tags()->attach($tag->id);
            }
        }
        return $tags;
    }

    /**通过字符串新增标签
     * @param $tagString
     * @param $question_id
     */
    public static function multiAddByName($tagString,$taggable)
    {
        if (!is_array($tagString)) {
            $tags = array_unique(explode(",",$tagString));
        } else {
            $tags = array_unique($tagString);
        }

        $tagIds = [];

        foreach($tags as $tag_name){

            if(!trim($tag_name)){
                continue;
            }

            $tag = self::firstOrCreate(['name'=>$tag_name]);

            if(!$taggable->tags->contains($tag->id))
            {
                $taggable->tags()->attach($tag->id);
                $tagIds[] = $tag->id;
            }
        }
        if ($tagIds) {
            TagsLogic::delCache();
        }
        return $tagIds;
    }

    public static function addByName(array $names){
        $tags = array_unique($names);
        $tagIds = [];

        foreach($tags as $tag_name){

            if(!trim($tag_name)){
                continue;
            }
            $tag = self::firstOrCreate(['name'=>$tag_name]);
            $tagIds[] = $tag->id;
        }
        if ($tagIds) {
            TagsLogic::delCache();
        }
        return $tagIds;
    }

    //通过tag id添加标签
    public static function multiSaveByIds($tags,$taggable)
    {
        if (!is_array($tags)) {
            $tags = array_unique(explode(",",$tags));
        } else {
            $tags = array_unique($tags);
        }

        /*删除所有标签关联*/
        if($tags){
            $taggable->tags()->detach();
        }

        foreach($tags as $tag_id){

            if(!trim($tag_id)){
                continue;
            }

            $tag = self::find($tag_id);

            if(!$taggable->tags->contains($tag->id))
            {
                $taggable->tags()->attach($tag->id);
            }
        }
        return $tags;
    }

    public static function getTagByName($tagName){
        $tags = self::where('name',$tagName)->get();
        $ignores = [
            8,//拒绝回答
            9,//行业
            10,//产品类型
            30,//活动报名
            31,//项目机遇
            33,//动态频道
            34,//小哈公社
            35,//观点洞见
            36,//新闻动态

        ];
        foreach ($tags as $tag) {
            if (in_array($tag->category_id,$ignores)) continue;
        }
        if (!isset($tag)) throw new ApiException(ApiException::BAD_REQUEST);
        return $tag;
    }




    /*搜索*/
    public static function search($word,$size=16)
    {
        $list = self::where('name','like',"$word%")->paginate($size);
        return $list;
    }



    public function questions()
    {
        return $this->morphedByMany('App\Models\Question', 'taggable');
    }

    public function submissions()
    {
        return $this->morphedByMany('App\Models\Submission', 'taggable');
    }

    public function userJobs()
    {
        return $this->morphedByMany('App\Models\UserInfo\JobInfo', 'taggable');
    }

    public function userProjects()
    {
        return $this->morphedByMany('App\Models\UserInfo\ProjectInfo', 'taggable');
    }

    public function answers()
    {
        return $this->morphedByMany('App\Models\Answer', 'taggable');
    }


    public function articles()
    {
        return $this->morphedByMany('App\Models\Article', 'taggable');
    }



    public function followers()
    {
        return $this->morphToMany('App\Models\UserData', 'source','attentions','source_id','user_id');
    }



    public function userTags(){
        return $this->hasMany('App\Models\UserTag','tag_id');
    }


    /*相关标签检索*/
    public function relations($pageSize=25)
    {
        return self::where(function($query){
                        $query->where('parent_id','=',$this->parent_id)
                              ->where('id','<>',$this->id);
                      })->orWhere('parent_id','=',$this->parent_id)
                        ->orderBy('followers','desc')->take($pageSize)->get();
    }


}
