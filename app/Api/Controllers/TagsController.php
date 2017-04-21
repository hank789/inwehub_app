<?php namespace App\Api\Controllers;
use App\Api\Controllers\Controller;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
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
            'tag_type' => 'required|in:1,2'
        ];

        $this->validate($request,$validateRules);
        $tag_type = $request->input('tag_type');

        switch($tag_type){
            case 1:
                //问题分类
                $category_name = 'question';
                break;
            case 2:
                //拒绝分类
                $category_name = 'answer_reject';
                break;
        }

        $question_c = Category::where('slug',$category_name)->first();
        $question_c_arr = Category::where('parent_id',$question_c->id)->where('status',1)->get();
        $tags = [];
        foreach($question_c_arr as $category){
            $tags[$category->name] = $category->tags()->pluck('name');
        }
        if(empty($tags)){
            //一维
            $tags[] = $question_c->tags()->pluck('name');
        }

        return self::createJsonData(true,$tags);
    }

}