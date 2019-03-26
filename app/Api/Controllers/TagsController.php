<?php namespace App\Api\Controllers;
use App\Events\Frontend\System\ImportantNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Company\CompanyData;
use App\Models\Doing;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: hank.huiwang@gmail.com
 */

class TagsController extends Controller {

    public function load(Request $request, JWTAuth $JWTAuth){
        $validateRules = [
            'tag_type' => 'required|in:1,2,3,4,5,6,8'
        ];

        $this->validate($request,$validateRules);
        $tag_type = $request->input('tag_type');
        if ($request->input('inwehub_user_device') != 'weapp_dianping') {
            try {
                $user = $JWTAuth->parseToken()->authenticate();
                if ($tag_type == 8 && empty($user->mobile)) {
                    return self::createJsonData(false,[],ApiException::USER_NEED_VALID_PHONE,ApiException::$errorMessages[ApiException::USER_NEED_VALID_PHONE]);
                }
            } catch (\Exception $e) {
                $user = new \stdClass();
                $user->id = 0;
                $user->name = '游客';
                $user->mobile = '';
                if ($tag_type == 8) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
            }
        }

        $word = $request->input('word');

        $sort = $request->input('sort');

        $limit = $request->input('limit',0);


        $data = TagsLogic::loadTags($tag_type,$word,'value',$sort);
        if ($limit) {
            $data['tags'] = array_slice($data['tags'],0,$limit);
        }

        return self::createJsonData(true,$data);
    }

    public function tagInfo(Request $request, JWTAuth $JWTAuth){
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
            $user->mobile = '';
        }
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        $is_followed = 0;
        $followAttention = Attention::where('user_id',$user->id)->where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->first();
        if ($followAttention) $is_followed = 1;
        $attentions = Attention::where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->simplePaginate(10);
        $attentionUsers = [];
        foreach ($attentions as $attention) {
            $attentionUsers[] = [
                'id' => $attention->user_id,
                'name' => $attention->user->name,
                'avatar' => $attention->user->avatar
            ];
        }
        $data = $tag->toArray();
        $data['is_followed'] = $is_followed;
        $data['followed_users'] = $attentionUsers;
        $this->logUserViewTags($user->id,[$tag]);
        return self::createJsonData(true,$data);
    }

    //产品服务详情
    public function productInfo(Request $request, JWTAuth $JWTAuth) {
        $validateRules = [
            'tag_name' => 'required'
        ];
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
            $user->mobile = '';
        }
        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        $is_followed = 0;
        $followAttention = Attention::where('user_id',$user->id)->where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->first();
        if ($followAttention) $is_followed = 1;
        $data = $this->getTagProductInfo($tag);
        $data['is_followed'] = $is_followed;
        $data['seo'] = [
            'title' => $tag->name,
            'description' => $tag->summary,
            'keywords' => implode(',',array_column($data['categories']??[],'name')+array_column($data['vendor']?:[],'name')),
            'published_time' => (new Carbon($tag->created_at))->toAtomString()
        ];
        $this->doing($user,Doing::ACTION_VIEW_DIANPING_PRODUCT_INFO,'',0,$tag->name,'',0,0,'',config('app.mobile_url').'#/dianping/product/'.rawurlencode($tag->name));
        return self::createJsonData(true,$data);
    }

    //产品点评列表
    public function productReviewList(Request $request,JWTAuth $JWTAuth) {
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));

        $tag = Tag::getTagByName($tag_name);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
            $user->mobile = '';
        }

        $query = Submission::where('status',1)->where('category_id',$tag->id);
        $submissions = $query->orderBy('is_recommend','desc')->orderBy('id','desc')->paginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $list[] = $submission->formatListItem($user, false);
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //提交产品
    public function submitProduct(Request $request) {
        $validateRules = [
            'name' => 'required|min:1',
            'logo' => 'required',
            'category_ids' => 'required',
            'company' => 'required|min:1',
            'summary' => 'required|min:1',
        ];
        $this->validate($request,$validateRules);
        $tag = Tag::where('name',$request->input('name'))->first();
        if ($tag) {
            return self::createJsonData(true,['name'=>$tag->name],ApiException::PRODUCT_TAG_ALREADY_EXIST,'产品已存在');
        }
        $user = $request->user();
        $logo = $this->uploadImgs($request->input('logo'),'tags');
        $company = CompanyData::initCompanyData($request->input('company'),$user->id,-1);
        $category_ids = $request->input('category_ids');
        $tag = Tag::create([
            'name' => $request->input('name'),
            'logo' => $logo['img'][0],
            'category_id' => $category_ids[0],
            'summary' => $request->input('summary')
        ]);
        foreach ($category_ids as $category_id) {
            if ($category_id<=0) continue;
            $rel = TagCategoryRel::where('tag_id',$tag->id)->where('category_id',$category_id)->first();
            if (!$rel) {
                TagCategoryRel::create([
                    'tag_id' => $tag->id,
                    'category_id' => $category_id,
                    'type' => TagCategoryRel::TYPE_REVIEW,
                    'status' => 0
                ]);
            }
        }
        Tag::multiAddByIds($tag->id,$company);
        event(new ImportantNotify('用户'.formatSlackUser($user).'提交了产品['.$tag->name.']'));
        return self::createJsonData(true,$tag->toArray());
    }

    public function feedbackProduct(Request $request) {
        $validateRules = [
            'product' => 'required|min:1',
            'type' => 'required',
            'content' => 'required|min:1',
        ];
        $this->validate($request,$validateRules);
        $tag = Tag::where('name',$request->input('product'))->first();
        if (!$tag) {
            return self::createJsonData(true,[],ApiException::PRODUCT_TAG_NOT_EXIST,'产品不存在');
        }
        $user = $request->user();
        $fields = [];
        $fields[] = [
            'title'=>'类型',
            'value'=>$request->input('type')
        ];
        $fields[] = [
            'title'=>'内容',
            'value'=>$request->input('content')
        ];
        if ($request->input('images')) {
            $logo = $this->uploadImgs($request->input('images'),'tags');
            $fields[] = [
                'title'=>'图片',
                'value'=>implode(',',$logo['img'])
            ];
        }
        event(new ImportantNotify('用户'.$user->id.'['.$user->name.']反馈产品['.$request->input('product').']',$fields));
        return self::createJsonData(true);
    }

    public function hotProduct(Request $request) {
        $hotProducts = [];
        $limit = $request->input('limit',10);
        $ids = RateLimiter::instance()->zRevrange('product-daily-hot-'.date('Ymd'),0,$limit-1);
        foreach ($ids as $id => $hotScore) {
            $tag = Tag::find($id);
            $info = Tag::getReviewInfo($tag->id);
            $hotProducts[] = [
                'id' => $tag->id,
                'name' => $tag->name,
                'logo' => $tag->logo,
                'review_count' => $info['review_count'],
                'review_average_rate' => $info['review_average_rate']
            ];
        }
        return self::createJsonData(true,$hotProducts);
    }

    //产品列表
    public function productList(Request $request) {
        $return = $this->tagProductList($request);
        return self::createJsonData(true, $return);
    }

    //精华点评列表
    public function getRecommendReview(Request $request,JWTAuth $JWTAuth) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $submissions = Submission::where('is_recommend',1)->where('status',1)->where('type','review')->orderBy('rate','desc')->simplePaginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
            $user->mobile = '';
        }
        foreach ($submissions as $submission) {
            $reviewInfo = Tag::getReviewInfo($submission->category_id);
            $tag = Tag::find($submission->category_id);
            $list[] = [
                'id' => $submission->id,
                'title' => formatHtml(strip_tags($submission->title)),
                'rate_star' => $submission->rate_star,
                'created_at' => (string)$submission->created_at,
                'slug' => $submission->slug,
                'url' => '/c/'.$submission->category_id.'/'.$submission->slug,
                'user'  => [
                    'id'    => $submission->hide?'':$submission->user->id ,
                    'uuid'  => $submission->hide?'':$submission->user->uuid,
                    'name'  => $submission->hide?'匿名':$submission->user->name,
                    'is_expert' => $submission->hide?0:$submission->user->is_expert,
                    'avatar'=> $submission->hide?config('image.user_default_avatar'):$submission->user->avatar
                ],
                'tag' => [
                    'id' => $submission->category_id,
                    'name' => $tag->name,
                    'logo' => $tag->logo,
                    'review_count' => $reviewInfo['review_count'],
                    'review_average_rate' => $reviewInfo['review_average_rate'],
                ]
            ];
        }
        $return['data'] = $list;
        $this->doing($user,Doing::ACTION_VIEW_DIANPING_INDEX,'',0,'');
        return self::createJsonData(true, $return);
    }

    //获取产品分类列表
    public function getProductCategories(Request $request) {
        $parent_id = $request->input('parent_id',0);
        $list = Cache::get('tags:product_categories_list_'.$parent_id);
        if (!$list) {
            $list = Category::getProductCategories($parent_id);
            Cache::forever('tags:product_categories_list_'.$parent_id,$list);
        }
        return self::createJsonData(true,$list);
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
                        foreach ($support_uids as $support_uid) {
                            $supporter = User::find($support_uid);
                            $supporters[] = [
                                'name' => $supporter->name,
                                'uuid' => $supporter->uuid
                            ];
                        }
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
                    'tags' => $question->tags()->wherePivot('is_display',1)->select('tag_id','name')->get()->toArray(),
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
                    'tags' => $question->tags()->wherePivot('is_display',1)->select('tag_id','name')->get()->toArray(),
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
        $userGroups = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('group_id')->toArray();
        $userPrivateGroups = [];
        foreach ($userGroups as $groupId) {
            $group = Group::find($groupId);
            if ($group->public == 0) $userPrivateGroups[$groupId] = $groupId;
        }
        $query = $tag->submissions();
        if ($userPrivateGroups) {
            $query = $query->Where(function ($query) use ($userPrivateGroups) {
                $query->where('public',1)->orWhereIn('group_id',$userPrivateGroups);
            });
        } else {
            $query = $query->where('public',1);
        }
        $submissions = $query->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
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
            $item['tags'] = $submission->tags()->wherePivot('is_display',1)->select('tag_id','name')->get()->toArray();
            $item['data']['current_address_name'] = $item['data']['current_address_name']??'';
            $item['data']['current_address_longitude'] = $item['data']['current_address_longitude']??'';
            $item['data']['current_address_latitude']  = $item['data']['current_address_latitude']??'';
            $item['group'] = $submission->group_id?Group::find($submission->group_id)->toArray():[];
            $list[] = $item;
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //提建议，谈工作，贺新春
    public function getThreeAc(){
        $tag1 = Tag::getTagByName('贺新春');
        $tag2 = Tag::getTagByName('谈工作');
        $tag3 = Tag::getTagByName('提建议');
        $tags = [
            ['value'=>$tag1->id,'text'=>$tag1->name],
            ['value'=>$tag2->id,'text'=>$tag2->name],
            ['value'=>$tag3->id,'text'=>$tag3->name]
        ];
        return self::createJsonData(true,['tags'=>$tags]);
    }

}