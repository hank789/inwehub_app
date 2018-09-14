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

    public function topInfo(Request $request, JWTAuth $JWTAuth) {
        try {
            $loginUser = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $loginUser = new \stdClass();
            $loginUser->id = -1;
            $loginUser->name = '游客';
        }
        $searchHistory = RateLimiter::instance()->hGetAll('search-user-count-'.$loginUser->id);
        $searchCount = RateLimiter::instance()->hGetAll('search-word-count');
        arsort($searchCount);
        //$topSearch = array_slice($searchCount,0,10,true);
        $topSearch = ['SAP','智能制造','区块链','数字化转型','转行','制造业','顾问','Oracle','ToB','金融'];
        $searchHistory = array_slice($searchHistory,0,20,true);
        return self::createJsonData(true,['history'=>array_keys($searchHistory),'top'=>$topSearch]);
    }

    public function suggest(Request $request) {
        $validateRules = [
            'search_word' => 'required|min:1',
        ];
        $this->validate($request,$validateRules);
        $searchWord = strtolower($request->input('search_word'));
        $searchCount = RateLimiter::instance()->hGetAll('search-word-count');
        arsort($searchCount);
        $suggest = [];
        foreach ($searchCount as $word=>$count) {
            if (str_contains(strtolower($word),$searchWord)) {
                $suggest[] = $word;
            }
        }
        return self::createJsonData(true,['suggest'=>$suggest]);
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
            $data[] = $question->formatListItem();
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
        $return = $submissions->toArray();
        $data = [];
        foreach ($submissions as $submission) {
            $data[] = $submission->formatListItem($user);
        }
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
