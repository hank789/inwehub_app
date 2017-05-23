<?php namespace App\Api\Controllers;
use App\Api\Controllers\Controller;
use App\Logic\TagsLogic;
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


        $data = TagsLogic::loadTags($tag_type,$word);

        return self::createJsonData(true,$data);
    }

}