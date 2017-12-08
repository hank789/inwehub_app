<?php namespace App\Logic;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * @author: wanghui
 * @date: 2017/5/23 下午10:06
 * @email: wanghui@yonglibao.com
 */

class TagsLogic {
    public static function loadTags($tag_type,$word,$tagKey='value'){

        $cache_key = 'tags:'.$tag_type.':'.$word;
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
                $category_name = ['industry'];
                break;
            case 4:
                //产品类型
                $category_name = ['product_type'];
                break;
            case 5:
                //用户擅长，包括问题分类[question]和产品类型[product_type]
                $category_name = ['question_sap','question_business','question_industry','product_type'];
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