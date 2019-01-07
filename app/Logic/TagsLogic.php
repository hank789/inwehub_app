<?php namespace App\Logic;
use App\Models\Category;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Services\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/5/23 下午10:06
 * @email: hank.huiwang@gmail.com
 */

class TagsLogic {
    public static function loadTags($tag_type,$word,$tagKey='value',$sort=0){

        $cache_key = 'tags:'.$tag_type.':'.$word.':'.$sort.':'.$tagKey;
        $cache = Cache::get($cache_key);
        if ($cache){
            return $cache;
        }
        $loadDefaultTags = false;

        switch($tag_type){
            case 1:
                //问题分类
                $category_name = ['question'];
                break;
            case 2:
                //拒绝分类
                $category_name = ['answer_reject'];
                break;
            case 3:
                //行业领域
                $category_name = ['question_industry'];
                break;
            case 4:
                //产品类型
                $category_name = ['product_type'];
                break;
            case 5:
                //用户擅长，包括问题分类[question]和产品类型[product_type]
                $category_name = Category::where('slug','like','question_%')->get()->pluck('slug')->toArray();
                $category_name2 = Category::where('type','enterprise_review')->where('grade',0)->get()->pluck('slug')->toArray();
                $category_name = array_merge($category_name,$category_name2);
                $loadDefaultTags = true;
                break;
            case 6:
                //领域
                $category_name = Category::where('slug','region')->get()->pluck('slug')->toArray();
                break;
            case 7:
                //产品服务
                $category_name = Category::where('type','enterprise_review')->where('grade',0)->get()->pluck('slug')->toArray();
                break;
            case 8:
                // 用户角色
                $category_name = ['role'];
                break;
            case 'all':
                $category_name = Category::where('grade',0)->get()->pluck('slug')->toArray();
                $loadDefaultTags = true;
                break;
        }
        $tags = [];
        if ($tag_type == 'allC') {
            $level = 1;
            $tagsAll = Tag::where('name','like',$word.'%')->orderByRaw('case when name like "'.$word.'" then 0 else 2 end')->take(100)->get();
            foreach ($tagsAll as $tag) {
                $tags[] = [
                    $tagKey => $tag->id,
                    'text'  => $tag->name
                ];
            }
        } else {
            $level = 2;
            $question_c = Category::whereIn('slug',$category_name)->get()->pluck('id')->toArray();
            $question_c_arr = Category::whereIn('parent_id',$question_c)->where('status',1)->orderBy('sort','asc')->get();

            foreach($question_c_arr as $category){
                $query = $category->tags();
                if(trim($word)){
                    $query = $query->where('name','like','%'.$word.'%')->orderByRaw('case when name like "'.$word.'" then 0 else 2 end');
                }
                $result = $query->take(100)->get();
                $item = [];
                $children = [];
                $item[$tagKey] = $category->id;
                $item['text'] = $category->name;
                foreach($result as $val){
                    $children[] = [
                        $tagKey => $val->id,
                        'text'  => $val->name
                    ];
                }
                $item['children'] = $children;
                $tags[] = $item;

            }
            if(empty($tags)){
                $level = 1;
                //一维
                if ($loadDefaultTags) {
                    $question_c[] = 0;
                }
                $tagQuery = TagCategoryRel::whereIn('tag_category_rel.category_id',$question_c)->leftJoin('tags','tag_id','=','tags.id');
                if (trim($word)) {
                    $tagQuery = $tagQuery->where('name','like','%'.$word.'%');
                }
                $tags2 = $tagQuery->select('tags.id','tags.name')->distinct()->take(100)->get();

                foreach ($tags2 as $tag) {
                    $tags[] = [
                        $tagKey => $tag->id,
                        'text'  => $tag->name
                    ];
                }
            }
            //如果热门排序
            if ($sort == 1) {
                $tagIds = array_column($tags,$tagKey);
                $query =  Taggable::select('tag_id',DB::raw('COUNT(id) as total_num'))
                    ->whereIn('tag_id',$tagIds);

                $taggables = $query->groupBy('tag_id')
                    ->orderBy('total_num','desc')
                    ->get();
                $tags = [];
                foreach ($taggables as $taggable) {
                    $tagInfo = Tag::find($taggable->tag_id);
                    $tags[] = [
                        $tagKey => $tagInfo->id,
                        'text'  => $tagInfo->name
                    ];
                }
            } else {
                usort($tags, function ($a, $b) {
                    if (strlen($a['text'])==strlen($b['text'])) return 0;
                    return (strlen($a['text'])<strlen($b['text']))?-1:1;
                });
            }
        }

        $data = [];
        $data['tags'] = $tags;
        $data['level'] = $level;
        Cache::forever($cache_key,$data);
        return $data;
    }

    public static function delCache() {
        $prefix = config('cache.prefix');
        $keys = Redis::connection()->keys($prefix.':tags:*');
        if ($keys) Redis::connection()->del($keys);
    }

    public static function delRelatedProductsCache() {
        $prefix = config('cache.prefix');
        $keys = Redis::connection()->keys($prefix.':submission_related_products_*');
        if ($keys) Redis::connection()->del($keys);
        $keys = Redis::connection()->keys($prefix.':question_related_products_*');
        if ($keys) Redis::connection()->del($keys);
    }

    public static function delProductCache() {
        $prefix = config('cache.prefix');
        $keys = Redis::connection()->keys($prefix.':tags:product_list_*');
        if ($keys) Redis::connection()->del($keys);
    }

    public static function formatTags($tags){
        $data = [];
        foreach($tags as $tag){
            $data[] = [
                'value' => $tag->id,
                'text'  => $tag->name
            ];
        }
        return $data;
    }

    public static function cacheProductTags(Tag $tag) {
        RateLimiter::instance()->hSet('product_tags',$tag->id,$tag->name);
    }

    public static function getContentTags($content) {
        if (strlen($content)>6) {
            $tags = RateLimiter::instance()->hGetAll('product_tags');
            $res = searchKeys($content,$tags,100);
            if ($res) {
                return array_column($res,0);
            }
            return [];
        }
        return [];
    }

    public static function getRegionTags($content) {
        if (strlen($content)>6) {
            $tags = [];
            $regions = TagsLogic::loadTags(6,'')['tags'];
            $relationTags = [];
            foreach ($regions as $region) {
                $tag = Tag::find($region['value']);
                $description = strip_tags($tag->description);
                if ($description) {
                    $description = str_replace('，',',',$description);
                    $ts = explode(',',$description);
                    foreach ($ts as $t) {
                        $relationTags[$t] = $region['text'];
                        $tags[] = $t;
                    }
                }
                $tags[] = $region['text'];
            }
            $res = searchKeys($content,$tags,100);
            if ($res) {
                $result =  array_column($res,0);
                foreach ($result as $key=>$i) {
                    if (isset($relationTags[$i])) {
                        unset($result[$key]);
                        $result[] = $relationTags[$i];
                    }
                }
                return array_unique($result);
            }
            return [];
        }
        return [];
    }

}