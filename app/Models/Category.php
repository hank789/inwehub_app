<?php

namespace App\Models;

use App\Models\Relations\MorphManyCommentsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property int $parent_id
 * @property int $grade
 * @property string $name
 * @property string $icon
 * @property string $slug
 * @property string $type
 * @property int $sort
 * @property string $role_id
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Article[] $articles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Authentication[] $experts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Question[] $questions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereGrade($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereIcon($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereParentId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereRoleId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereSlug($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereSort($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereStatus($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model implements HasMedia
{
    use MorphManyCommentsTrait,HasMediaTrait;
    protected $table = 'categories';
    protected $fillable = ['parent_id','grade','name','slug','summary','icon','status','sort','type','role_id','category_id'];


    public static function boot()
    {
        parent::boot();

        /*监听删除事件*/
        static::deleting(function($category){
            $category->questions()->update(['category_id'=>0]);
            $category->articles()->update(['category_id'=>0]);
            $category->experts()->update(['category_id'=>0]);
            TagCategoryRel::where('category_id',$category->id)->delete();
        });
    }

    /**
     * 获取用户问题
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany('App\Models\Question','category_id');
    }


    /**
     * 获取用户问题
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles()
    {
        return $this->hasMany('App\Models\Article','category_id');
    }

    /**
     * 获取用户问题
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany('App\Models\Tag','tag_category_rel');
    }

    /**
     * 获取用户问题
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function experts()
    {
        return $this->hasMany('App\Models\Authentication','category_id');
    }


    public static function makeOptionTree($categories=null)
    {
        if(!$categories){
            $categories = self::loadFromCache('all');
        }

        $optionTree = '';
        foreach ($categories as $category) {
            if ($category->parent_id == 0) {
                $optionTree .= "<option value=\"{$category->id}\">{$category->name}</option>";
                $optionTree .= self::makeChildOption($categories, $category->id, 1);
            }
        }
        return $optionTree;

    }


    public static function makeChildOption($categories, $parentId, $depth = 1){
        $childTree = '';
        foreach ($categories as $category) {
            if ( $parentId == $category->parent_id ) {
                $childTree .= "<option value=\"{$category->id}\">";
                $depthStr = str_repeat("--", $depth);
                $childTree .= $depth ? "&nbsp;&nbsp;|{$depthStr}&nbsp;{$category->name}</option>" : "{$category->name}</option>";
                $childTree .= self::makeChildOption($categories, $category->id, $depth + 1);
            }
        }
        return $childTree;
    }

    public static function getChildrenIds($parent_id) {
        $categories = Category::where('parent_id',$parent_id)->get();
        $list = [];
        foreach ($categories as $category) {
            if ($category->grade == 1) {
                //具有子分类
                $children = self::getChildrenIds($category->id);
                $list = array_merge($list,$children);
            }
            $list[] = $category->id;

        }
        return $list;
    }

    public static function getProductCategories($parent_id) {
        if (!$parent_id) {
            $categories = Category::where('status',1)->whereIn('slug',['enterprise_product','enterprise_service'])->get();
        } else {
            $categories = Category::where('status',1)->where('parent_id',$parent_id)->get();
        }
        $list = [];
        foreach ($categories as $category) {
            $children = [];
            if ($category->grade == 1) {
                //具有子分类
                $children = self::getProductCategories($category->id);
                $children_count =  Category::where('status',1)->where('parent_id',$category->id)->count();
            } else {
                //$children_count = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->where('category_id',$category->id)->count();
                $children_count = 0;
            }
            $list[] = [
                'id' => $category->id,
                'name' => $category->name,
                'children_count' => $children_count,
                'children' => $children
            ];
        }
        return $list;
    }

    public static function loadFromCache($type='all', $root = false, $last = false){

        /*$globalCategories = Cache::rememberForever('global_all_categories',function() {
            return self::where('status','>',0)->orderBy('sort','asc')->orderBy('created_at','asc')->get();
        });*/
        $query = self::where('status','>',0);
        if($root){
            $query->where('parent_id',0);
        }
        if ($last) {
            $query->where('grade',0);
        }
        $globalCategories = $query->orderBy('sort','asc')->orderBy('created_at','asc')->get();

        /*返回所有分类*/
        if($type == 'all'){
            return $globalCategories;
        }

        /*按类文档型返回分类*/
        $categories = [];
        foreach( $globalCategories as $category ){
            if (is_array($type)) {
                foreach ($type as $item) {
                    if( str_contains($category->type,$item) ){
                        $categories[] = $category;
                    }
                }
            } else {
                if( str_contains($category->type,$type) ){
                    $categories[] = $category;
                }
            }
        }
        return $categories;

    }

}
