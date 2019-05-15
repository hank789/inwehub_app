<?php

namespace App\Models;

use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Relations\BelongsToCategoryTrait;
use App\Services\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;

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
class Tag extends Model implements HasMedia
{
    use BelongsToCategoryTrait, Searchable, HasMediaTrait;
    protected $table = 'tags';
    protected $fillable = ['name', 'logo', 'summary','description','followers', 'category_id', 'reviews', 'is_pro'];

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
            TagCategoryRel::where('tag_id',$tag->id)->delete();
            /*删除用户标签*/
            UserTag::where('tag_id','=',$tag->id)->delete();
            Taggable::where('tag_id',$tag->id)->delete();
            if(Setting()->get('xunsearch_open',0) == 1){
                App::offsetGet('search')->delete($tag);
            }
            Taggable::where('tag_id',$tag->id)->delete();
            RateLimiter::instance()->hSet('ignore_tags',$tag->name,$tag->id);
        });
    }

    public function categories()
    {
        return $this->belongsToMany('App\Models\Category','tag_category_rel');
    }

    /**
     * 通过字符串添加标签
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

    /**
     * 通过字符串新增标签
     */
    public static function multiAddByName($tagString,$taggable,$category_id=0)
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

            $tag = self::where(['name'=>$tag_name])->first();
            if (!$tag) {
                $tag = self::create(['name'=>$tag_name]);
                if ($category_id > 0) {
                    TagCategoryRel::create([
                        'tag_id' => $tag->id,
                        'category_id' => $category_id
                    ]);
                }
            }

            if(!$taggable->tags->contains($tag->id))
            {
                $taggable->tags()->attach($tag->id,['is_display'=>$category_id==1?0:1]);
                $tagIds[] = $tag->id;
            }
        }
        if ($tagIds) {
            TagsLogic::delCache();
        }
        return $tagIds;
    }

    public static function multiAddByIds($tags,$taggable)
    {
        if (!is_array($tags)) {
            $tags = array_unique(explode(",",$tags));
        } else {
            $tags = array_unique($tags);
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
        $tag = self::where('name',$tagName)->first();
        if (!$tag && is_numeric($tagName)) {
            $tag = self::find($tagName);
        }
        return $tag;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $rel = TagCategoryRel::where('tag_id',$this->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->first();
        $status = 1;
        $type = TagCategoryRel::TYPE_DEFAULT;
        if ($rel) {
          $status = 1;
          $type = TagCategoryRel::TYPE_REVIEW;
        } else {
            $rel = TagCategoryRel::where('tag_id',$this->id)->where('type',TagCategoryRel::TYPE_REVIEW)->first();
            if ($rel) {
                $status = 0;
                $type = TagCategoryRel::TYPE_REVIEW;
            }
        }
        $keywords = $this->getKeywords();

        $fields =  [
            'name' => strtolower($this->name),
            'keywords' => strtolower(strip_tags($keywords)),
            'status' => $status,
            'reviews' => $this->reviews,
            'type' => $type
        ];
        if (config('app.env') != 'production') {
            unset($fields['status']);
            unset($fields['type']);
            unset($fields['keywords']);
        }
        return $fields;
    }

    public function getKeywords() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $keywords = $description['keywords']??'';
        } else {
            $keywords = $this->description;
        }
        return $keywords;
    }

    public function getCoverPic() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $cover_pic = $description['cover_pic']??'';
        } else {
            $cover_pic = '';
        }
        return $cover_pic;
    }

    public function getIntroducePic() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $introduce_pic = $description['introduce_pic']??[];
        } else {
            $introduce_pic = [];
        }
        return $introduce_pic;
    }

    public function getAdvanceDesc() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $advance_desc = $description['advance_desc']??'';
        } else {
            $advance_desc = '';
        }
        return $advance_desc;
    }

    public function getWebsite() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $website = $description['website']??'';
        } else {
            $website = '';
        }
        return $website;
    }

    public function getGzhNames() {
        $contents = ContentCollection::where('content_type',ContentCollection::CONTENT_TYPE_TAG_WECHAT_GZH)
            ->where('source_id',$this->id)
            ->get();
        $names = [];
        foreach ($contents as $content) {
            $names[]= $content->content['wx_hao'];
        }
        return $names;
    }

    public function getOnlyShowRelateProducts() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $advance_desc = $description['only_show_relate_products']??0;
        } else {
            $advance_desc = 0;
        }
        return $advance_desc;
    }

    public function getRelateProducts() {
        $description = json_decode($this->description,true);
        if (is_array($description)) {
            $rel_tags = $description['rel_tags']??[];
        } else {
            $rel_tags = [];
        }
        if ($rel_tags) {
            $tags = [];
            foreach ($rel_tags as $id) {
                $tags[] = Tag::find($id);
            }
            return $tags;
        }
        return $rel_tags;
    }

    public function setDescription(array $desc) {
        $description = json_decode($this->description,true);
        if (!$description) {
            $description = [];
        }
        $description = array_merge($description,$desc);
        $this->description = json_encode($description);
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


    public function relationReviews($pageSize=25)
    {
        $return = [];
        //优先取后台配置的
        $rel_tags = $this->getRelateProducts();
        foreach ($rel_tags as $rel_tag) {
            $reviewInfo = Tag::getReviewInfo($rel_tag->id);
            $return[] = [
                'id' => $rel_tag->id,
                'name' => $rel_tag->name,
                'logo' => $rel_tag->logo,
                'review_count' => $reviewInfo['review_count'],
                'review_average_rate' => $reviewInfo['review_average_rate']
            ];
            if (count($return) >= $pageSize) return $return;
        }
        $only_show_relate_products = $this->getOnlyShowRelateProducts();
        if ($only_show_relate_products) return $return;
        $category_ids = TagCategoryRel::where('tag_id',$this->id)->orderBy('support_rate','desc')->pluck('category_id')->toArray();
        $album_cids = Category::whereIn('id',$category_ids)->where('type','product_album')->pluck('id')->toArray();
        $related_tags = TagCategoryRel::WhereIn('category_id',count($album_cids)?$album_cids:$category_ids)->where('type',TagCategoryRel::TYPE_REVIEW)
            ->where('tag_id','!=',$this->id)
            ->select('tag_id')->distinct()
            ->orderBy('reviews','desc')->take($pageSize*2)->get();
        $used = [];
        foreach ($related_tags as $related_tag) {
            if (isset($used[$related_tag->tag_id])) continue;
            $used[$related_tag->tag_id] = $related_tag->tag_id;
            $reviewInfo = Tag::getReviewInfo($related_tag->tag_id);
            $tag = Tag::find($related_tag->tag_id);
            $return[] = [
                'id' => $tag->id,
                'name' => $tag->name,
                'logo' => $tag->logo,
                'review_count' => $reviewInfo['review_count'],
                'review_average_rate' => $reviewInfo['review_average_rate']
            ];
            if (count($return) >= $pageSize) return $return;
        }
        if (count($return) < $pageSize && count($album_cids)) {
            $other_cids = array_diff($category_ids,$album_cids);
            if ($other_cids) {
                $related_tags = TagCategoryRel::WhereIn('category_id',$other_cids)->where('type',TagCategoryRel::TYPE_REVIEW)
                    ->where('tag_id','!=',$this->id)
                    ->select('tag_id')->distinct()
                    ->orderBy('reviews','desc')->take($pageSize*2)->get();
                foreach ($related_tags as $related_tag) {
                    if (isset($used[$related_tag->tag_id])) continue;
                    $used[$related_tag->tag_id] = $related_tag->tag_id;
                    $reviewInfo = Tag::getReviewInfo($related_tag->tag_id);
                    $tag = Tag::find($related_tag->tag_id);
                    $return[] = [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'logo' => $tag->logo,
                        'review_count' => $reviewInfo['review_count'],
                        'review_average_rate' => $reviewInfo['review_average_rate']
                    ];
                    if (count($return) >= $pageSize) return $return;
                }
            }
        }
        return $return;
    }

    public function relationProductAlbum($pageSize=25) {

    }

    public function countMorph() {
        return Taggable::where('tag_id',$this->id)->count();
    }

    public static function getReviewInfo($id) {
        $tag = [];
        $tag['review_count'] = Submission::where('status',1)->where('category_id',$id)->count();
        $sumRate = Submission::where('status',1)->where('category_id',$id)->sum('rate_star');
        $tag['review_average_rate'] = $tag['review_count']?bcdiv($sumRate,$tag['review_count'],1):0;
        $tag['review_average_rate'] = floatval($tag['review_average_rate']);
        return $tag;
    }

    public function getProductCacheInfo() {
        return Cache::get('weapp_product_info_'.$this->id);
    }

    public function setProductCacheInfo(array $data) {
        Cache::put('weapp_product_info_'.$this->id,$data,60*24*4);
    }

}
