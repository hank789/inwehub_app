<?php namespace App\Api\Controllers;

use App\Events\Frontend\System\SystemNotify;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

class SearchController extends Controller
{

    protected function searchNotify($user,$searchWord,$typeName='',$searchResult=''){
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.$typeName.'搜索['.$searchWord.']'.$searchResult));
        RateLimiter::instance()->hIncrBy('search-word-count',$searchWord,1);
        RateLimiter::instance()->hIncrBy('search-user-count-'.$user->id,$searchWord,1);
    }

    public function suggest(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);

    }

    public function user(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $loginUser = $request->user();
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
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[用户]',',搜索结果'.$users->total());
        return self::createJsonData(true, $return);
    }

    public function tag(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $loginUser = $request->user();
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
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[标签]',',搜索结果'.$tags->total());
        return self::createJsonData(true, $return);
    }

    public function question(Request $request,JWTAuth $JWTAuth)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        try {
            $loginUser = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $loginUser = new \stdClass();
            $loginUser->id = 0;
            $loginUser->name = '游客';
        }
        $questions = Question::search($request->input('search_word'))->where(function($query) {$query->where('is_recommend',1)->where('question_type',1)->orWhere('question_type',2);})->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $data = [];
        foreach ($questions as $question) {
            $item = [
                'id' => $question->id,
                'question_type' => $question->question_type,
                'description'  => $question->title,
                'tags' => $question->tags()->wherePivot('is_display',1)->get()->toArray()
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
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[问答]',',搜索结果'.$questions->total());
        return self::createJsonData(true, $return);
    }

    public function submission(Request $request)
    {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $userGroups = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('group_id')->toArray();
        $userPrivateGroups = [];
        foreach ($userGroups as $groupId) {
            $group = Group::find($groupId);
            if ($group->public == 0) $userPrivateGroups[$groupId] = $groupId;
        }

        $query = Submission::search($request->input('search_word'))->where('status',1);
        if ($userPrivateGroups && false) {
            $query = $query->Where(function ($query) use ($userPrivateGroups) {
                $query->where('public',1)->orWhereIn('group_id',$userPrivateGroups);
            });
        } else {
            //$query = $query->where('public',1);
        }
        $submissions = $query->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
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
            $item['title'] = strip_tags($item['title']);
            $item['is_upvoted'] = $upvote ? 1 : 0;
            $item['is_bookmark'] = $bookmark ? 1: 0;
            $item['tags'] = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $group = Group::find($submission->group_id);
            $item['group'] = $group->toArray();
            $item['group']['subscribers'] = $group->getHotIndex();
            $item['category_name'] = $group->name;
            $data[] = $item;
        }
        $return = $submissions->toArray();
        $return['data'] = $data;
        $this->searchNotify($user,$request->input('search_word'),'在栏目[分享]',',搜索结果'.$submissions->total());
        return self::createJsonData(true, $return);
    }

    public function group(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $groups = Group::search($request->input('search_word'))->where('audit_status',Group::AUDIT_STATUS_SUCCESS)->orderBy('subscribers', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $groups->toArray();
        $return['data'] = [];
        foreach ($groups as $group) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
            $is_joined = -1;
            if ($groupMember) {
                $is_joined = $groupMember->audit_status;
            }
            if ($user->id == $group->user_id) {
                $is_joined = 3;
            }
            $return['data'][] = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'logo' => $group->logo,
                'public' => $group->public,
                'subscribers' => $group->getHotIndex(),
                'articles'    => $group->articles,
                'is_joined'  => $is_joined,
                'owner' => [
                    'id' => $group->user->id,
                    'uuid' => $group->user->uuid,
                    'name' => $group->user->name,
                    'avatar' => $group->user->avatar,
                    'description' => $group->user->description,
                    'is_expert' => $group->user->is_expert
                ]
            ];
        }
        $this->searchNotify($user,$request->input('search_word'),'在栏目[圈子]',',搜索结果'.$groups->total());
        return self::createJsonData(true,$return);
    }
}
