<?php namespace App\Api\Controllers;
use App\Api\Controllers\Controller;
use App\Logic\TagsLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: wanghui@yonglibao.com
 */

class TagsController extends Controller {

    public function load(Request $request){
        $validateRules = [
            'tag_type' => 'required|in:1,2,3,4,5'
        ];

        $this->validate($request,$validateRules);
        $tag_type = $request->input('tag_type');

        $word = $request->input('word');

        $sort = $request->input('sort');

        $data = TagsLogic::loadTags($tag_type,$word,'value',$sort);

        return self::createJsonData(true,$data);
    }

    public function tagInfo(Request $request){
        $validateRules = [
            'tag_id' => 'required|integer'
        ];

        $this->validate($request,$validateRules);
        $tag_id = $request->input('tag_id');
        $tag = Tag::findOrFail($tag_id);
        return self::createJsonData(true,$tag->toArray());
    }

    //标签相关用户
    public function users(Request $request){
        $validateRules = [
            'tag_id' => 'required|integer'
        ];

        $this->validate($request,$validateRules);
        $tag_id = $request->input('tag_id');
        $tag = Tag::findOrFail($tag_id);
        $loginUser = $request->user();
        $userTags = $tag->userTags()->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $userTags->toArray();
        $data = [];
        foreach ($userTags as $userTag) {
            $user = $userTag->user;
            $item = [];
            $skillTags = Tag::select('name')->whereIn('id',$user->userSkillTag()->pluck('tag_id'))->distinct()->pluck('name')->toArray();
            if ($skillTags) {
                $item['description'] = '擅长';
                foreach ($skillTags as $skillTag) {
                    $item['description'] .= '"'.$skillTag.'"';
                }
            } else {
                $tagIds = $user->userTags()->select("tag_id")->distinct()->where('answers','>',0)->get()->pluck('tag_id');
                if ($tagIds) {
                    $answerTags = Tag::select('name')->whereIn('id',$tagIds)->distinct()->pluck('name')->toArray();
                    $item['description'] = '曾在"'.implode('"',$answerTags).'"下有回答';
                }
            }
            $is_followed = 0;
            if($loginUser) {
                $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($user))->where('source_id','=',$user->id)->first();
                if ($attention){
                    $is_followed = 1;
                }
            }
            $item['id'] = $user->id;
            $item['uuid'] = $user->uuid;
            $item['name'] = $user->name;
            $item['avatar_url'] = $user->avatar;
            $item['is_expert'] = $user->is_expert;
            $item['is_followed'] = $is_followed;
            $data[] = $item;
        }
        $return['data'] = $data;
        return self::createJsonData(true,$return);
    }

    //标签相关问答
    public function questions(Request $request) {
        $validateRules = [
            'tag_id' => 'required|integer'
        ];

        $this->validate($request,$validateRules);
        $tag_id = $request->input('tag_id');
        $tag = Tag::findOrFail($tag_id);
        $loginUser = $request->user();
        $questions = $tag->questions()->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $questions->toArray();
        $data = [];
        foreach ($questions as $question) {

        }

    }

    //标签相关动态
    public function submissions(Request $request){

    }

}