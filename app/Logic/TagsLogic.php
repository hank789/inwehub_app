<?php namespace App\Logic;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * @author: wanghui
 * @date: 2017/5/23 下午10:06
 * @email: wanghui@yonglibao.com
 */

class TagsLogic {
    public static function loadTags($tag_type,$word){

        $cache_key = 'tags:'.$tag_type.':'.$word;
        $cache = Cache::get($cache_key);
        if ($cache){
            return $cache;
        }

        switch($tag_type){
            case 1:
                //问题分类
                $category_name = 'question';
                break;
            case 2:
                //拒绝分类
                $category_name = 'answer_reject';
                break;
            case 3:
                //行业领域
                $category_name = 'industry';
                break;
            case 4:
                //产品类型
                $category_name = 'product_type';
                break;
        }

        $level = 2;
        $question_c = Category::where('slug',$category_name)->first();
        $question_c_arr = Category::where('parent_id',$question_c->id)->where('status',1)->get();
        $tags = [];
        foreach($question_c_arr as $category){
            $query = $category->tags();
            if(trim($word)){
                $query = $query->where('name','like',$word.'%');
            }
            $item = [];
            $children = [];
            $item['value'] = $category->id;
            $item['text'] = $category->name;
            foreach($query->get() as $val){
                $children[] = [
                    'value' => $val->id,
                    'text'  => $val->name
                ];
            }
            $item['children'] = $children;
            $tags[] = $item;

        }
        if(empty($tags)){
            $level = 1;
            //一维
            $query_c = $question_c->tags();
            if(trim($word)){
                $query_c = $query_c->where('name','like',$word.'%');
            }
            foreach($query_c->get() as $val){
                $tags[] = [
                    'value' => $val->id,
                    'text'  => $val->name
                ];
            }
        }
        $data = [];
        $data['tags'] = $tags;
        $data['level'] = $level;
        Cache::forever($cache_key,$data);
        return $data;
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