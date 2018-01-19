<?php namespace App\Api\Controllers;
use App\Api\Controllers\Controller;
use App\Logic\TagsLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use App\Models\User;
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

        $limit = $request->input('limit',0);


        $data = TagsLogic::loadTags($tag_type,$word,'value',$sort);
        if ($limit) {
            $data['tags'] = array_slice($data['tags'],0,$limit);
        }

        return self::createJsonData(true,$data);
    }

    public function tagInfo(Request $request){
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        return self::createJsonData(true,$tag->toArray());
    }

    //标签相关用户
    public function users(Request $request){
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
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
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        $user = $request->user();
        $questions = $tag->questions()->where('status','>=',1)->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $questions->toArray();
        $list = [];
        foreach ($questions as $question) {
            if ($question->question_type == 1) {
                if($question->status >= 6 ){
                    $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
                } else {
                    continue;
                }
                $supporters = [];
                $is_pay_for_view = false;
                $is_self = $user->id == $question->user_id;
                $is_answer_author = false;

                if ($bestAnswer) {
                    //是否回答者
                    if ($bestAnswer->user_id == $user->id) {
                        $is_answer_author = true;
                    }
                    $support_uids = Support::where('supportable_type','=',get_class($bestAnswer))->where('supportable_id','=',$bestAnswer->id)->take(20)->pluck('user_id');
                    if ($support_uids) {
                        $supporters = User::select('name','uuid')->whereIn('id',$support_uids)->get()->toArray();
                    }
                    $payOrder = $bestAnswer->orders()->where('user_id',$user->id)->where('return_param','view_answer')->first();
                    if ($payOrder) {
                        $is_pay_for_view = true;
                    }
                }

                $list[] = [
                    'id' => $question->id,
                    'question_type' => $question->question_type,
                    'user_id' => $question->user_id,
                    'description'  => $question->title,
                    'tags' => $question->tags()->select('tag_id','name')->get()->toArray(),
                    'hide' => $question->hide,
                    'price' => $question->price,
                    'status' => $question->status,
                    'created_at' => (string)$question->created_at,
                    'answer_user_id' => $bestAnswer ? $bestAnswer->user->id : '',
                    'answer_username' => $bestAnswer ? $bestAnswer->user->name : '',
                    'answer_user_title' => $bestAnswer ? $bestAnswer->user->title : '',
                    'answer_user_company' => $bestAnswer ? $bestAnswer->user->company : '',
                    'answer_user_is_expert' => $bestAnswer && $bestAnswer->user->userData->authentication_status == 1 ? 1 : 0,
                    'answer_user_avatar_url' => $bestAnswer ? $bestAnswer->user->avatar : '',
                    'answer_time' => $bestAnswer ? (string)$bestAnswer->created_at : '',
                    'comment_number' => $bestAnswer ? $bestAnswer->comments : 0,
                    'average_rate'   => $bestAnswer ? $bestAnswer->getFeedbackRate() : 0,
                    'support_number' => $bestAnswer ? $bestAnswer->supports : 0,
                    'supporter_list' => $supporters,
                    'is_pay_for_view' => ($is_self || $is_answer_author || $is_pay_for_view)
                ];
            } else {
                $is_followed_question = 0;
                $attention_question = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->first();
                if ($attention_question) {
                    $is_followed_question = 1;
                }
                $answer_uids = Answer::where('question_id',$question->id)->select('user_id')->distinct()->take(5)->pluck('user_id')->toArray();
                $answer_users = [];
                if ($answer_uids) {
                    $answer_users = User::whereIn('id',$answer_uids)->select('uuid','name')->get()->toArray();
                }
                $list[] = [
                    'id' => $question->id,
                    'question_type' => $question->question_type,
                    'user_id' => $question->user_id,
                    'description'  => $question->title,
                    'tags' => $question->tags()->select('tag_id','name')->get()->toArray(),
                    'hide' => $question->hide,
                    'price' => $question->price,
                    'status' => $question->status,
                    'created_at' => (string)$question->created_at,
                    'question_username' => $question->hide ? '匿名' : $question->user->name,
                    'question_user_is_expert' => $question->hide ? 0 : ($question->user->userData->authentication_status == 1 ? 1 : 0),
                    'question_user_avatar_url' => $question->hide ? config('image.user_default_avatar') : $question->user->avatar,
                    'answer_num' => $question->answers,
                    'answer_user_list' => $answer_users,
                    'follow_num' => $question->followers,
                    'is_followed_question' => $is_followed_question
                ];
            }
        }
        $return['data'] = $list;
        return self::createJsonData(true,$return);

    }

    //标签相关动态
    public function submissions(Request $request){
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        $user = $request->user();
        $submissions = $tag->submissions()->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$submission['id'])
                ->where('supportable_type',Submission::class)
                ->exists();
            $bookmark = Collection::where('user_id',$user->id)
                ->where('source_id',$submission['id'])
                ->where('source_type',Submission::class)
                ->exists();
            $item = $submission->toArray();
            $item['title'] = strip_tags($item['title'],'<a><span>');
            $item['is_upvoted'] = $upvote ? 1 : 0;
            $item['is_bookmark'] = $bookmark ? 1: 0;
            $item['tags'] = $submission->tags()->select('tag_id','name')->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

}