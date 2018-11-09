<?php namespace App\Api\Controllers;
use App\Logic\TagsLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Company\CompanyData;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Models\User;
use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: hank.huiwang@gmail.com
 */

class TagsController extends Controller {

    public function load(Request $request){
        $validateRules = [
            'tag_type' => 'required|in:1,2,3,4,5,6,8'
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
        $loginUser = $request->user();
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        $is_followed = 0;
        $followAttention = Attention::where('user_id',$loginUser->id)->where('source_type','=',get_class($tag))->where('source_id','=',$tag->id)->first();
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
        $this->logUserViewTags($loginUser->id,[$tag]);
        return self::createJsonData(true,$data);
    }

    //产品服务详情
    public function productInfo(Request $request) {
        $validateRules = [
            'tag_name' => 'required'
        ];
        $user = $request->user();
        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $tag = Tag::getTagByName($tag_name);
        $reviewInfo = Tag::getReviewInfo($tag->id);
        $data = $tag->toArray();
        $data['review_count'] = $reviewInfo['review_count'];
        $data['review_average_rate'] = $reviewInfo['review_average_rate'];
        $data['related_tags'] = $tag->relationReviews(4);
        $categoryRels = TagCategoryRel::where('tag_id',$tag->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->orderBy('review_average_rate','desc')->get();
        foreach ($categoryRels as $key=>$categoryRel) {
            $category = Category::find($categoryRel->category_id);
            $rate = TagCategoryRel::where('category_id',$category->id)->where('review_average_rate','>',$categoryRel->review_average_rate)->count();
            $data['categories'][] = [
                'id' => $category->id,
                'name' => $category->name,
                'rate' => $rate?:1
            ];
        }
        $data['vendor'] = '';
        $taggable = Taggable::where('tag_id',$tag->id)->where('taggable_type',CompanyData::class)->first();
        if ($taggable) {
            $companyData = CompanyData::find($taggable->taggable_id);
            $data['vendor'] = [
                'id'=>$taggable->taggable_id,
                'name'=>$companyData->name
            ];
        }
        //推荐股问
        $recommendUsers = UserTag::where('tag_id',$tag->id)->where('user_id','!=',$user->id)->orderBy('articles','desc')->take(5)->get();
        foreach ($recommendUsers as $recommendUser) {
            $userTags = $recommendUser->user->userTag()->orderBy('skills','desc')->pluck('tag_id');
            $skillTag = Tag::find($userTags[0]);
            $data['recommend_users'][] = [
                'name' => $recommendUser->user->name,
                'id'   => $recommendUser->user_id,
                'uuid' => $recommendUser->user->uuid,
                'is_expert' => $recommendUser->user->is_expert,
                'avatar_url' => $recommendUser->user->avatar,
                'skill' => $skillTag?$skillTag->name:''
            ];
        }
        return self::createJsonData(true,$data);
    }

    //产品点评列表
    public function productReviewList(Request $request) {
        $validateRules = [
            'tag_name' => 'required'
        ];

        $this->validate($request,$validateRules);
        $tag_name = $request->input('tag_name');
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));

        $tag = Tag::getTagByName($tag_name);
        $user = $request->user();

        $query = Submission::where('category_id',$tag->id)->where('status',1);
        $submissions = $query->orderBy('is_recommend','desc')->orderBy('id','desc')->paginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $list[] = $submission->formatListItem($user, false);
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //产品列表
    public function productList(Request $request) {
        $category_id = $request->input('category_id',0);
        $orderBy = $request->input('orderBy',1);
        $query = TagCategoryRel::where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
        if ($category_id) {
            $category = Category::find($category_id);
            if ($category->grade == 1) {
                $children = Category::getChildrenIds($category_id);
                $children[] = $category_id;
                $query = $query->whereIn('category_id',array_unique($children));
            } else {
                $query = $query->where('category_id',$category_id);
            }
        }
        switch ($orderBy) {
            case 1:
                $query = $query->orderBy('review_average_rate','desc');
                break;
            case 2:
                $query = $query->orderBy('reviews','desc');
                break;
            default:
                $query = $query->orderBy('updated_at','desc');
                break;
        }
        $tags = $query->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $tags->toArray();
        $list = [];
        $used = [];
        foreach ($tags as $tag) {
            if (!isset($used[$tag->tag_id])) {
                $model = Tag::find($tag->tag_id);
                $info = Tag::getReviewInfo($model->id);
                $used[$tag->tag_id] = $tag->tag_id;
                $list[] = [
                    'id' => $model->id,
                    'name' => $model->name,
                    'logo' => $model->logo,
                    'review_count' => $info['review_count'],
                    'review_average_rate' => $info['review_average_rate']
                ];
            }
        }
        $return['data'] = $list;
        return self::createJsonData(true, $return);
    }

    //精华点评列表
    public function getRecommendReview(Request $request) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $submissions = Submission::where('type','review')->where('is_recommend',1)->orderBy('rate','desc')->simplePaginate($perPage);
        $return = $submissions->toArray();
        $list = [];
        foreach ($submissions as $submission) {
            $reviewInfo = Tag::getReviewInfo($submission->category_id);
            $tag = Tag::find($submission->category_id);
            $list[] = [
                'id' => $submission->id,
                'title' => strip_tags($submission->title),
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
            $item['group'] = Group::find($submission->group_id)->toArray();
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