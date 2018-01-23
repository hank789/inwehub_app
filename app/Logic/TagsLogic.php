<?php namespace App\Logic;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Taggable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/**
 * @author: wanghui
 * @date: 2017/5/23 下午10:06
 * @email: wanghui@yonglibao.com
 */

class TagsLogic {
    public static function loadTags($tag_type,$word,$tagKey='value',$sort=0){

        $cache_key = 'tags:'.$tag_type.':'.$word.':'.$sort;
        $cache = Cache::get($cache_key);
        if ($cache){
            return $cache;
        }

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
                $category_name = ['question_sap','question_business','question_industry','product_type', 'question_slution','question_other'];
                break;
            case 'all':
                $category_name = ['question_sap','question_business','question_industry','product_type', 'question_slution','question_other'];
                break;
        }

        $level = 2;
        $question_c = Category::whereIn('slug',$category_name)->get()->pluck('id')->toArray();
        $question_c_arr = Category::whereIn('parent_id',$question_c)->where('status',1)->orderBy('sort','asc')->get();
        $tags = [];
        foreach($question_c_arr as $category){
            $query = $category->tags();
            if(trim($word)){
                $query = $query->where('name','like','%'.$word.'%');
            }
            $item = [];
            $children = [];
            $item[$tagKey] = $category->id;
            $item['text'] = $category->name;
            foreach($query->get() as $val){
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
            foreach ($question_c as $cid) {
                $c_model = Category::find($cid);
                $query_c = $c_model->tags();
                if(trim($word)){
                    $query_c = $query_c->where('name','like','%'.$word.'%');
                }
                foreach($query_c->get() as $val){
                    $tags[] = [
                        $tagKey => $val->id,
                        'text'  => $val->name
                    ];
                }
            }
            $defaultTags = Tag::where('category_id',0)->get();
            foreach ($defaultTags as $defaultTag) {
                $tags[] = [
                    $tagKey => $defaultTag->id,
                    'text'  => $defaultTag->name
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

}