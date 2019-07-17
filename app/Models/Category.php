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

    public function getRegionTag() {
        $tag = '';
        if ($this->parent_id == 1359) {
            $maps = [
                1404 => 170,//金融行业信息化及专业解决方案=> 信息化
                1403 => 182,//UiPath产品实施伙伴（China）=> 企业服务
                1402 => 20,//TMS运输管理系统=>供应链
                1401 => 170,//PPM企业级项目管理平台=>信息化
                1400 => 170,//WMS仓库管理系统=>信息化
                1399 => 139,//SAP产品实施伙伴（China）=>sap
                1398 => 16300,//MES制造企业生产过程执行系统=>智能制造
                1397 => 12209,//企业视频及语音会议=>云服务
                1396 => 182,//RPA 机器人流程自动化=>企业服务
                1394 => 182,//一体化的财税工商服务平台=>企业服务
                1393 => 170,//BPM与电子化流程管理=>信息化
                1392 => 182,//企业中台与敏捷组织转型=>企业服务
                1391 => 181,//本土龙头咨询公司->咨询行业
                1388 => 182,//新零售行业解决方案=>企业服务
                1387 => 434,//发票管理平台=>财务
                1384 => 181,//咨询行业的巨头们=>咨询行业
                1382 => 434,//企业金融服务平台（票据）=>财务
                1381 => 434,//预算、费控与发票管理=>财务
                1380 => 20,//从供应商、寻源到采购和付款的平台集成方案=>供应链
                1379 => 20,//供应链与需求计划平台=>供应链
                1377 => 250,//企业级区块链基础服务平台=>区块链
                1375 => 182,//企业服务领域自媒体=>企业服务
                1374 => 12209,//企业云ERP第一选择=>云服务
                1373 => 182,//第三方电子签名行业玩家	=>企业服务
                1371 => 182,//国内第三方电子签名行业玩家	=>企业服务
                1370 => 29,//劳动力管理平台大全	=>人力资源
                //1369 => 0,PaaS物联网平台大盘点=>IoT
                1368 => 170,//舆情监控工具	=>信息化
                1367 => 170,//表单调查工具大全	=>信息化
                1366 => 29,//HRMS=>人力资源
                1365 => 170,//ERP软件=>信息化
                1364 => 16300,//PLM=>智能制造
                1363 => 12209,//基础架构服务IaaS供应商=>云服务
                1362 => 170,//OA与协同=>信息化
                1361 => 170,//CRM=>信息化
                1360 => 170,//分析与商业智能	=>信息化
            ];
            if (isset($maps[$this->id])) {
                $tag = $maps[$this->id];
            }
        }
        return $tag;

    }

}
