<?php namespace App\Api\Controllers;

use App\Events\Frontend\System\SystemNotify;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SearchController extends Controller
{

    protected function searchNotify($user,$searchWord,$typeName=''){
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.$typeName.'搜索['.$searchWord.']'));
        RateLimiter::instance()->hIncrBy('search-word-count',$searchWord,1);
        RateLimiter::instance()->hIncrBy('search-user-count-'.$user->id,$searchWord,1);
    }
    public function user(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $loginUser = $request->user();
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[用户]');
        $users = User::search($request->input('search_word'))->where('status',1)->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($users as $user) {
            $is_followed = 0;
            $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($user))->where('source_id','=',$user->id)->first();
            if ($attention){
                $is_followed = 1;
            }
            $item = [];
            $item['id'] = $user->id;
            $item['user_id'] = $user->id;
            $item['uuid'] = $user->uuid;
            $item['user_name'] = $user->name;
            $item['company'] = $user->company;
            $item['title'] = $user->title;
            $item['user_avatar_url'] = $user->avatar;
            $item['is_expert'] = $user->is_expert;
            $item['description'] = $user->description;
            $item['is_followed'] = $is_followed;
            $data[] = $item;
        }
        $return = $users->toArray();
        $return['data'] = $data;
        return self::createJsonData(true, $return);
    }

    public function tag(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $loginUser = $request->user();
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[标签]');
        $tags = Tag::search($request->input('search_word'))->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($tags as $tag) {
            $item = [];
            $item['id'] = $tag->id;
            $item['name'] = $tag->name;
            $data[] = $item;
        }
        $return = $tags->toArray();
        $return['data'] = $data;
        return self::createJsonData(true, $return);
    }

    public function question(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $loginUser = $request->user();
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[问答]');
        $questions = Question::search($request->input('search_word'))->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($questions as $question) {
            $item = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'description'  => $question->title,
                'tags' => $question->tags()->get()->toArray()
            ];
            if($question->question_type == 1){
                $item['comment_number'] = 0;
                $item['average_rate'] = 0;
                $item['support_number'] = 0;
                $bestAnswer = $question->answers()->where('adopted_at','>',0)->first();
                if ($bestAnswer) {
                    $item['comment_number'] = $bestAnswer->comments;
                    $item['average_rate'] = $bestAnswer->getFeedbackRate();
                    $item['support_number'] = $bestAnswer->supports;
                }
            } else {
                $item['answer_number'] = $question->answers;
                $item['follow_number'] = $question->followers;
            }
            $data[] = $item;
        }
        $return = $questions->toArray();
        $return['data'] = $data;
        return self::createJsonData(true, $return);
    }

    public function submission(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $this->searchNotify($user,$request->input('search_word'),'在栏目[分享]');
        $submissions = Submission::search($request->input('search_word'))->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
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
            $item['tags'] = $submission->tags()->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $data[] = $item;
        }
        $return = $submissions->toArray();
        $return['data'] = $data;
        return self::createJsonData(true, $return);
    }



}
