<?php namespace App\Api\Controllers;
use App\Api\Controllers\Controller;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: wanghui@yonglibao.com
 */

class TagsController extends Controller {

    public function load(Request $request){
        $validateRules = [
            'tag_type' => 'required|in:1,2,3,4'
        ];

        $this->validate($request,$validateRules);
        $tag_type = $request->input('tag_type');

        $word = $request->input('word');
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
            $tags[$category->name] = $query->pluck('name');
        }
        if(empty($tags)){
            $level = 1;
            //一维
            $query_c = $question_c->tags();
            if(trim($word)){
                $query_c = $query_c->where('name','like',$word.'%');
            }
            $tags = $query_c->pluck('name')->toArray();
        }
        $data = [];
        $data['tags'] = $tags;
        $data['level'] = $level;

        return self::createJsonData(true,$data);
    }

}