<?php namespace App\Api\Controllers;

use App\Events\Frontend\System\ImportantNotify;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Company\CompanyData;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

class SearchController extends Controller
{

    protected function searchNotify($user,$searchWord,$typeName='',$searchResult=''){
        event(new ImportantNotify('用户'.$user->id.'['.$user->name.']'.$typeName.'搜索['.$searchWord.']'.$searchResult));
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
        //$searchCount = RateLimiter::instance()->hGetAll('search-word-count');
        //arsort($searchCount);
        //$topSearch = array_slice($searchCount,0,10,true);
        //$topSearch = ['SAP','智能制造','区块链','数字化转型','转行','制造业','顾问','Oracle','ToB','金融'];
        $topSearch = ['企业服务','自动化','CRM','数字化转型','制造业','供应链','SAP','税务','Salesforce','麦肯锡'];
        $searchHistory = array_reverse(array_slice($searchHistory,count($searchHistory)-10,10,true));
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

    public function all(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $word = $request->input('search_word');
        $perPage = 3;
        $return = [
            'submission' => [
                'total' => 0,
                'list'  => []
            ],
            'question' => [
                'total' => 0,
                'list'  => []
            ],
            'group' => [
                'total' => 0,
                'list'  => []
            ],
            'product' => [
                'total' => 0,
                'list'  => []
            ],
            'review' => [
                'total' => 0,
                'list'  => []
            ]
        ];
        //搜索分享
        $query = Submission::search(formatElasticSearchTitle($word))->where('status',1)->where('public',1);
        if (config('app.env') == 'production') {
            $query = $query->where('product_type',1);
            $submissions = $query->orderBy('rate.keyword', 'desc')->paginate($perPage);
        } else {
            $submissions = $query->orderBy('rate', 'desc')->paginate($perPage);
        }

        $return['submission']['total'] = $submissions->total();
        foreach ($submissions as $submission) {
            $return['submission']['list'][] = $submission->formatListItem($user);
        }
        //搜索问答
        $questions = Question::search($word)->where(function($query) {$query->where('is_recommend',1)->where('question_type',1)->orWhere('question_type',2);})->orderBy('rate', 'desc')->paginate($perPage);
        $return['question']['total'] = $questions->total();
        foreach ($questions as $question) {
            $return['question']['list'][] = $question->formatListItem();
        }
        //搜索圈子
        $groups = Group::search($word)->where('audit_status',Group::AUDIT_STATUS_SUCCESS)->orderBy('subscribers', 'desc')->paginate($perPage);
        $return['group']['total'] = $groups->total();
        foreach ($groups as $group) {
            $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
            $is_joined = -1;
            if ($groupMember) {
                $is_joined = $groupMember->audit_status;
            }
            if ($user->id == $group->user_id) {
                $is_joined = 3;
            }
            $return['group']['list'][] = [
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
        //搜索产品
        $query = Tag::search(formatElasticSearchTitle($word));
        if (config('app.env') == 'production') {
            $query = $query->where('type',TagCategoryRel::TYPE_REVIEW)
                ->where('status',1);
        }
        if (config('app.env') == 'production') {
            $tags = $query->orderBy('reviews.keyword', 'desc')
                ->paginate($perPage);
        } else {
            $tags = $query->orderBy('reviews', 'desc')
                ->paginate($perPage);
        }

        $return['product']['total'] = $tags->total();
        foreach ($tags as $tag) {
            $info = Tag::getReviewInfo($tag->id);
            $return['product']['list'][] = [
                'id' => $tag->id,
                'name' => $tag->name,
                'logo' => $tag->logo,
                'review_count' => $info['review_count'],
                'review_average_rate' => $info['review_average_rate']
            ];
        }
        //搜索点评
        $query = Submission::search(formatElasticSearchTitle($word))->where('status',1);
        if (config('app.env') == 'production') {
            $query = $query->where('product_type',2);
            $submissions = $query->orderBy('rate.keyword', 'desc')->paginate($perPage);
        } else {
            $query = $query->where('type','review');
            $submissions = $query->orderBy('rate', 'desc')->paginate($perPage);
        }

        $return['review']['total'] = $submissions->total();
        foreach ($submissions as $submission) {
            $return['review']['list'][] = $submission->formatListItem($user,false);
        }
        $this->searchNotify($user,$word,'综合',$return['review']['total']+$return['product']['total']+$return['group']['total']+$return['question']['total']+$return['submission']['total']);
        return self::createJsonData(true,$return);
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

    public function tagProduct(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $loginUser = $request->user();
        $query = Tag::search(formatElasticSearchTitle($request->input('search_word')));
        if (config('app.env') == 'production') {
            $query = $query->where('type',TagCategoryRel::TYPE_REVIEW)
                ->where('status',1);
            $tags = $query->orderBy('reviews.keyword', 'desc')
                ->paginate(Config::get('inwehub.api_data_page_size'));
        } else {
            $tags = $query->orderBy('reviews', 'desc')
                ->paginate(Config::get('inwehub.api_data_page_size'));
        }

        $data = [];
        $ids = [];
        foreach ($tags as $key=>$tag) {
            if ($key === 0 && strtolower($tag->name)!=strtolower($request->input('search_word'))) {
                $match = Tag::getTagByName($request->input('search_word'));
                if ($match) {
                    $matchRel = TagCategoryRel::select(['tag_id'])->where('tag_id',$match->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->first();
                    if ($matchRel) {
                        $ids[$match->id] = $match->id;
                        $info = Tag::getReviewInfo($match->id);
                        $data[] = [
                            'id' => $match->id,
                            'name' => $match->name,
                            'logo' => $match->logo,
                            'review_count' => $info['review_count'],
                            'review_average_rate' => $info['review_average_rate']
                        ];
                    }
                }
            }
            if (isset($ids[$tag->id])) continue;
            $ids[$tag->id] = $tag->id;
            $info = Tag::getReviewInfo($tag->id);
            $data[] = [
                'id' => $tag->id,
                'name' => $tag->name,
                'logo' => $tag->logo,
                'review_count' => $info['review_count'],
                'review_average_rate' => $info['review_average_rate']
            ];
        }
        $return = $tags->toArray();
        $return['data'] = $data;
        $this->searchNotify($loginUser,$request->input('search_word'),'在栏目[产品]',',搜索结果'.$tags->total());
        return self::createJsonData(true, $return);
    }

    public function reviews(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $query = Submission::search(formatElasticSearchTitle($request->input('search_word')))->where('status',1);
        if (config('app.env') == 'production') {
            $query = $query->where('product_type',2);
            $submissions = $query->orderBy('rate.keyword', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        } else {
            $query = $query->where('type','review');
            $submissions = $query->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        }

        $return = $submissions->toArray();
        $data = [];
        foreach ($submissions as $submission) {
            $data[] = $submission->formatListItem($user,false);
        }
        $return['data'] = $data;
        $this->searchNotify($user,$request->input('search_word'),'在栏目[点评]',',搜索结果'.$submissions->total());
        return self::createJsonData(true, $return);
    }

    public function productCategory(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $word = $request->input('search_word');
        $user = $request->user();
        $categories = Category::where('type','enterprise_review')->where('status',1)
            ->where('name','like',"%$word%")
            ->orderBy('id', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $categories->toArray();
        $data = [];
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->id,
                'name' => $category->name
            ];
        }
        $return['data'] = $data;
        $this->searchNotify($user,$word,'在栏目[分类]',',搜索结果'.$categories->total());
        return self::createJsonData(true, $return);
    }

    public function company(Request $request) {
        $validateRules = [
            'search_word' => 'required',
        ];
        $this->validate($request,$validateRules);
        $word = $request->input('search_word');
        $user = $request->user();
        $companies = CompanyData::where('audit_status',1)
            ->where('name','like',"%$word%")
            ->orderBy('id', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $companies->toArray();
        $userData = $user->userData;
        $data = [];
        foreach ($companies as $company) {
            $tags = $company->tags()->pluck('name')->toArray();
            if (!is_numeric($userData->longitude) || !is_numeric($userData->latitude) || !is_numeric($company->latitude) || !is_numeric($company->longitude)) {
                $distance = '未知';
            } else {
                $distance = getDistanceByLatLng($company->longitude,$company->latitude,$userData->longitude,$userData->latitude);
                $distance = bcadd($distance,0,0);
            }
            $data[] = [
                'id' => $company->id,
                'name' => $company->name,
                'logo' => $company->logo,
                'address_province' => $company->address_province,
                'tags' => $tags,
                //'distance' => $distance,
                'distance_format' => distanceFormat($distance),
            ];
        }
        $return['data'] = $data;
        $this->searchNotify($user,$word,'在栏目[公司]',',搜索结果'.$companies->total());
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
        $userPrivateGroups = false;
        /*$userGroups = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('group_id')->toArray();
        $userPrivateGroups = [];
        foreach ($userGroups as $groupId) {
            $group = Group::find($groupId);
            if ($group->public == 0) $userPrivateGroups[$groupId] = $groupId;
        }*/

        $query = Submission::search(formatElasticSearchTitle($request->input('search_word')))->where('status',1)->where('public',1);
        if (config('app.env') == 'production') {
            $query = $query->where('product_type',1);
            $submissions = $query->orderBy('rate.keyword', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        } else {
            $submissions = $query->orderBy('rate', 'desc')->paginate(Config::get('inwehub.api_data_page_size'));
        }

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
