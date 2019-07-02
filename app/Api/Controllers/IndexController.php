<?php namespace App\Api\Controllers;
use App\Events\Frontend\System\ImportantNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Logic\QuillLogic;
use App\Logic\TagsLogic;
use App\Models\Activity\Coupon;
use App\Models\AddressBook;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Authentication;
use App\Models\Comment;
use App\Models\Doing;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Notice;
use App\Models\Question;
use App\Models\Submission;
use App\Models\RecommendRead;
use App\Models\Support;
use App\Models\Tag;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Translation\Dumper\IniFileDumper;
use Tymon\JWTAuth\JWTAuth;

/**
 * @author: wanghui
 * @date: 2017/5/12 下午5:55
 * @email: hank.huiwang@gmail.com
 */

class IndexController extends Controller {
    public function home(Request $request, JWTAuth $JWTAuth){
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $expire_at = '';

        $show_invitation_coupon = false;
        $show_ad = false;
        if($user->id){
            //检查活动时间
            $ac_first_ask_begin_time = Setting()->get('ac_first_ask_begin_time');
            $ac_first_ask_end_time = Setting()->get('ac_first_ask_end_time');
            if($ac_first_ask_begin_time && $ac_first_ask_end_time && $ac_first_ask_begin_time<=date('Y-m-d H:i') && $ac_first_ask_end_time>date('Y-m-d H:i')){
                $is_first_ask = !$user->userData->questions;
                //用户是否已经领过红包
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->first();
                if(!$coupon && $is_first_ask){
                    $show_ad = true;
                }
                if($coupon && $coupon->coupon_status == Coupon::COUPON_STATUS_PENDING  && $coupon->expire_at > date('Y-m-d H:i:s'))
                {
                    $expire_at = $coupon->expire_at;
                }
            }
            //新注册领取受邀注册红包
            //检查活动时间
            $ac_invitation_coupon_begin_time = Setting()->get('ac_invitation_coupon_begin_time');
            $ac_invitation_coupon_end_time = Setting()->get('ac_invitation_coupon_end_time');
            if ($user->rc_uid && strtotime($user->created_at) >= strtotime($ac_invitation_coupon_begin_time) && $ac_invitation_coupon_begin_time && $ac_invitation_coupon_end_time && $ac_invitation_coupon_begin_time <=date('Y-m-d H:i') && $ac_invitation_coupon_end_time > date('Y-m-d H:i')) {
                //用户是否已经领过红包
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_NEW_REGISTER_INVITATION)->first();
                if(!$coupon){
                    $show_invitation_coupon = true;
                }
            }
        }

        //轮播图
        $notices = Notice::where('status',1)->orderBy('sort','desc')->take(5)->get()->toArray();
        foreach ($notices as &$notice) {
            $notice['url_www'] = $notice['url'][1]??'';
            $notice['url'] = $notice['url'][0]??'';
        }
        //当日热门圈子
        $hotGroups = [];
        /*$groupIds = RateLimiter::instance()->zRevrange('group-daily-hot-'.date('Ymd'),0,2);
        foreach ($groupIds as $groupId => $hotScore) {
            $group = Group::find($groupId);
            $hotGroups[] = [
                'id' => $groupId,
                'user_id' => $group->user_id,
                'name'    => $group->name,
                'description' => $group->description,
                'logo'    => $group->logo,
                'public'  => $group->public,
                'scores'  => $hotScore,
                'owner'   => [
                    'id' => $group->user_id,
                    'uuid' => $group->user->uuid,
                    'name' => $group->user->name,
                    'is_expert' => $group->user->is_expert,
                    'avatar' => $group->user->avatar
                ]
            ];
        }*/
        //当前用户是否有圈子未读信息
        $user_group_unread = 0;
        $new_message = [];
        if ($user->id && false) {
            $groupMembers = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('id','asc')->get();
            foreach ($groupMembers as $groupMember) {
                $group = $groupMember->group;
                $user_group_unread = RateLimiter::instance()->sIsMember('group_read_users:'.$group->id,$user->id)?0:1;
                if ($user_group_unread) {
                    $new_message[] = [
                        'text'=>'您的圈子有新动态！',
                        'link'=>'/group/my'
                    ];
                    break;
                }
            }
            $todo_task = $user->tasks()->where('status',0)->count();
            if ($todo_task > 0) {
                $new_message[] = [
                    'text'=>'您有'.$todo_task.'条待办事项！',
                    'link'=>'/task'
                ];
            }
            $addressBookCount = AddressBook::where('user_id',$user->id)->where('status',1)->count();
            if ($addressBookCount <= 0) {
                $new_message[] = [
                    'text'=>'来寻找你的通讯录好友！',
                    'link'=>'/userGuide/stepthree/app'
                ];
            }
        }

        $regions = TagsLogic::loadTags(6,'');
        $tags = $regions['tags'];

        $data = [
            'first_ask_ac' => ['show_first_ask_coupon'=>$show_ad,'coupon_expire_at'=>$expire_at],
            'invitation_coupon' => ['show'=>$show_invitation_coupon],
            'notices' => $notices,
            'recommend_experts' => [],
            'regions' => array_merge([['value'=>-1,'text'=>'热门']],$tags),
            'hot_groups' => $hotGroups,
            'user_group_unread' => $user_group_unread,
            'new_message' => $new_message
        ];
        //$this->doing($user,Doing::ACTION_VIEW_HOME,'',0,'核心页面');
        return self::createJsonData(true,$data);
    }


    public function getNextRecommendRead(Request $request) {
        $this->validate($request, [
            'source_id' => 'required|integer',
            'source_type' => 'required|integer',
        ]);
        $source_type = $request->input('source_type',1);
        $source_id = $request->input('source_id');
        $recommend = null;
        switch ($source_type) {
            case 1:
                //文章
                $recommend = RecommendRead::where('source_id',$source_id)->where('source_type',Submission::class)->first();
                break;
            case 2:
                //问答
                $recommend = RecommendRead::where('source_id',$source_id)->where('source_type',Question::class)->first();
                break;
        }
        if (!$recommend) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $next = RecommendRead::where('rate','<=',$recommend->rate)->where('id','!=',$recommend->id)->orderBy('rate','desc')->first();
        if ($next) {
            $item = $this->formatRecommendReadItem($next->toArray());
            return self::createJsonData(true,$item);
        }
        return self::createJsonData(true);
    }

    protected function formatRecommendReadItem($item) {
        $item['data']['title'] = strip_tags($item['data']['title']??'');
        switch ($item['read_type']) {
            case RecommendRead::READ_TYPE_SUBMISSION:
                // '发现分享';
                $object = Submission::find($item['source_id']);
                if (empty($item['data']['img'])) {
                    $item['data']['img'] = '';
                }
                if (is_array($item['data']['img'])) {
                    $item['data']['img'] = $item['data']['img'][0];
                }
                if (empty($item['data']['title'])) {
                    $item['data']['title'] = strip_tags($object->title);
                }
                $item['type_description'] = '';
                $item['data']['comment_number'] = $object->comments_number;
                $item['data']['support_number'] = $object->upvotes;
                $item['data']['view_number'] = $object->views;
                $item['data']['support_rate'] = $object->getSupportRate();
                $item['data']['body'] = '';
                $item['data']['url'] = '';
                $item['data']['domain'] = '';
                $item['data']['article_title'] = '';
                if ($object->type == 'link') {
                    $item['data']['domain'] = $object->data['domain'];
                    $item['data']['body'] = strip_tags($object->title);
                    $item['data']['article_title'] = strip_tags($object->data['title']);
                    $item['data']['url'] = formatThirdLink($object->data['url']);
                } elseif($object->type == 'text') {
                    $item['data']['body'] = str_limit(strip_tags($object->title), 300);
                } elseif($object->type == 'article') {
                    $item['data']['body'] = str_limit(QuillLogic::parseText($object->data['description']), 300);
                } else {
                    $item['data']['body'] = str_limit(strip_tags($object->title), 300);
                }
                break;
            case RecommendRead::READ_TYPE_PAY_QUESTION:
                // '专业问答';
                $object = Question::find($item['source_id']);
                $bestAnswer = $object->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get()->last();
                $item['type_description'] = '问';
                $item['data']['price'] = $object->price;
                $item['data']['average_rate'] = $bestAnswer->getFeedbackRate();
                $item['data']['view_number'] = $bestAnswer->views;
                $item['data']['comment_number'] = $bestAnswer->comments;
                $item['data']['support_number'] = $bestAnswer->supports;
                $item['data']['support_rate'] = $bestAnswer->getSupportRate();
                $item['data']['feedback_rate'] = $bestAnswer->getFeedbackAverage();
                break;
            case RecommendRead::READ_TYPE_FREE_QUESTION:
                // '互动问答';
                $object = Question::find($item['source_id']);
                $item['type_description'] = '问';
                $item['data']['answer_number'] = $object->answers;
                $item['data']['follower_number'] = $object->followers;
                $item['data']['view_number'] = $object->views;
                break;
            case RecommendRead::READ_TYPE_ACTIVITY:
                // '活动';
                break;
            case RecommendRead::READ_TYPE_PROJECT_OPPORTUNITY:
                // '项目机遇';
                break;
            case RecommendRead::READ_TYPE_FREE_QUESTION_ANSWER:
                // '互动问答回复';
                $object = Answer::find($item['source_id']);
                $item['type_description'] = '问';
                $item['data']['comment_number'] = $object->comments;
                $item['data']['support_number'] = $object->supports;
                $item['data']['view_number'] = $object->views;
                $item['data']['support_rate'] = $object->getSupportRate();
                $item['data']['feedback_rate'] = $object->getFeedbackAverage();
                break;
        }
        return $item;
    }

    public function getRelatedRecommend(Request $request, JWTAuth $JWTAuth) {
        $this->validate($request, [
            'source_id' => 'required|integer',
            'source_type' => 'required|integer',
        ]);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $source_type = $request->input('source_type',1);
        $source_id = $request->input('source_id');
        $cache_key = 'user_related_recommend_'.$source_type.'_'.$source_id.'_'.$user->id;
        $result = Cache::get($cache_key);
        if (!$result) {
            $perPage = $request->input('perPage',4);
            $recommend = $source = null;
            $views = [];
            $tags = null;
            $query = RecommendRead::where('audit_status',1);
            switch ($source_type) {
                case 0:
                    if ($user->id) {
                        $tags = $user->userTag()->orderBy('views','desc')->pluck('tag_id')->take(10)->toArray();
                    }
                    break;
                case 1:
                    //文章
                    $recommend = RecommendRead::where('source_id',$source_id)->where('source_type',Submission::class)->first();
                    $source = Submission::find($source_id);
                    break;
                case 2:
                    //问答
                    $recommend = RecommendRead::where('source_id',$source_id)->where('source_type',Question::class)->first();
                    $source = Question::find($source_id);
                    break;
            }
            if ($user->id) {
                $viewIds = Doing::where('user_id',$user->id)
                    ->where('source_type',Submission::class)
                    ->where('created_at','>=',date('Y-m-d H:i:s',strtotime('-14 days')))
                    ->select('source_id')->distinct()->pluck('source_id')->toArray();
                if ($viewIds) {
                    foreach ($viewIds as $viewId) {
                        $viewRecommend = RecommendRead::where('source_id',$viewId)->where('source_type',Submission::class)->first();
                        if ($viewRecommend) {
                            $views[] = $viewRecommend->id;
                        }
                    }
                }
                $recommendedIds = RateLimiter::instance()->sMembers('user-recommend-'.$user->id);
                $all = RecommendRead::where('audit_status',1)->count();
                $views = array_unique(array_merge($views,$recommendedIds));
                if ($all - count($views) <= 4) {
                    RateLimiter::instance()->sClear('user-recommend-'.$user->id);
                }
            }
            if ($recommend) {
                $tags = $recommend->tags()->pluck('tag_id')->toArray();
                $views[] = $recommend->id;
            } elseif($source) {
                $tags = $source->tags()->pluck('tag_id')->toArray();
            }
            $reads = [];
            $views = array_unique($views);
            if (count($views) >= 1) {
                $query = $query->whereNotIn('id',$views);
            }
            if ($tags) {
                $query = $query->whereHas('tags',function($query) use ($tags) {
                    $query->whereIn('tag_id', $tags);
                });
                $reads = $query->orderBy('rate','desc')->simplePaginate($perPage);
            }
            if (empty($reads) || $reads->count() < 4) {
                $query2 = RecommendRead::where('audit_status',1);
                $count = $query2->count();
                $rand = $perPage/$count * 100;
                $reads = $query2->where(DB::raw('RAND()'),'<=',$rand)->distinct()->orderBy(DB::raw('RAND()'))->simplePaginate($perPage);
            }
            $result = $reads->toArray();
            foreach ($result['data'] as &$item) {
                if ($user->id) {
                    RateLimiter::instance()->sAdd('user-recommend-'.$user->id,$item['id'],60 * 30);
                }
                $item = $this->formatRecommendReadItem($item);
            }
            Cache::put($cache_key,$result,3);
        }

        return self::createJsonData(true, $result);
    }

    public function readList(Request $request, JWTAuth $JWTAuth) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $page = $request->input('page',1);
        $alertMsg = '';
        $last_seen= '';
        $filterTag = $request->input('tagFilter','');
        $filterTagName = '全部';
        try {
            $user = $JWTAuth->parseToken()->authenticate();
            $last_seen = RateLimiter::instance()->hGet('user_read_last_seen',$user->id.'_'.$filterTag);
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        if ($filterTag != -1) {
            $query = Submission::where('status',1)->where('group_id',0)->where('type','!=','review');
            if ($filterTag) {
                $query = $query->whereHas('tags',function($query) use ($filterTag) {
                    $query->where('tag_id', $filterTag);
                });
                $tag = Tag::find($filterTag);
                $filterTagName = $tag->name;
            }
        } else {
            $query = RecommendRead::where('audit_status',1);
            $filterTagName = '推荐';
        }
        if ($filterTag) {
            $query = $query->orderBy('rate','desc');
        } else {
            $query = $query->orderBy('created_at','desc');
        }


        $reads = $query->simplePaginate($perPage);
        $result = $reads->toArray();
        if ($page == 1) {
            if ($last_seen) {
                $ids = $reads->pluck('id')->toArray();
                $newCount = array_search($last_seen,$ids);
                if ($newCount === false) {
                    $newCount = '20+';
                }
                if ($newCount) {
                    $alertMsg = '更新了'.$newCount.'条信息';
                } else {
                    $alertMsg = '暂无新信息';
                }
            } else {
                $alertMsg = '已为您更新';
            }
        }
        $list = [];
        $inwehub_user_device = $request->input('inwehub_user_device','web');
        foreach ($reads as $key=>$item) {
            if ($page == 1 && $key == 0) {
                $last_seen = $item->id;
            }
            if ($filterTag == -1 && $item->source_type != Submission::class) continue;
            if ($filterTag == -1) {
                $item = Submission::find($item->source_id);
            }
            $domain = $item->data['domain']??'';
            $link_url = $item->data['url']??'';
            if (!in_array($inwehub_user_device,['web','wechat']) && $domain == 'mp.weixin.qq.com') {
                if (!(str_contains($link_url, 'wechat_redirect') || str_contains($link_url, '__biz=') || str_contains($link_url, '/s/'))) {
                    $link_url = config('app.url').'/articleInfo/'.$item->id.'?inwehub_user_device='.$inwehub_user_device;
                }
            }
            if ($link_url) {
                $link_url = formatThirdLink($link_url);
            }
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$item->id)
                ->where('supportable_type',Submission::class)
                ->exists();
            $tags = [];
            if ($user->id > 0 && ($user->isRole('operatormanager') || $user->isRole('admin'))) {
                $tags = $item->tags()->select('tags.id','tags.name')->get()->toArray();
                if ($item->isRecommendRead()) {
                    $tags[] = [
                        'id' => -1,
                        'name'=>'推荐'
                    ];
                }
            }
            $img = $item->data['img']??'';
            if (is_array($img)) {
                if ($img) {
                    $img = $img[0];
                } else {
                    $img = '';
                }
            }
            $list[] = [
                'id'    => $item->id,
                'title' => trim(strip_tags($item->data['title']??$item->title)),
                'type'  => $item->type,
                'domain'    => $domain,
                'img'   => $img,
                'slug'      => $item->slug,
                'category_id' => $item->category_id,
                'is_upvoted'     => $upvote ? 1 : 0,
                'link_url'  => $link_url,
                'rate'  => (int)(substr($item->rate,8)?:0),
                'comment_number' => $item->comments_number,
                'support_number' => $item->upvotes,
                'share_number' => $item->share_number,
                'tags' => $tags,
                'created_at'=> (string)$item->created_at
            ];
        }
        if ($page == 1) {
            RateLimiter::instance()->hSet('user_read_last_seen',$user->id.'_'.$filterTag,$last_seen);
            event(new SystemNotify('用户'.$user->id.'['.$user->name.']打开首页-'.$filterTagName));
        }
        $result['data'] = $list;
        $result['alert_msg'] = $alertMsg;
        return self::createJsonData(true, $result);
    }

    public function dailyReport(Request $request, JWTAuth $JWTAuth) {
        $this->validate($request, [
            'date' => 'required',
        ]);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $list = [];
        $date = $request->input('date');
        $begin = date('Y-m-d 00:00:00',strtotime($date));
        $end = date('Y-m-d 23:59:59',strtotime($date));
        $recommends = RecommendRead::where('audit_status',1)->whereBetween('created_at',[$begin,$end])->orderBy('rate','desc')->take(10)->get();
        foreach ($recommends as $recommend) {
            $item = Submission::find($recommend->source_id);
            $domain = $item->data['domain']??'';
            $link_url = $item->data['url']??'';
            if ($domain == 'mp.weixin.qq.com') {
                if (!(str_contains($link_url, 'wechat_redirect') || str_contains($link_url, '__biz=') || str_contains($link_url, '/s/'))) {
                    $link_url = config('app.url').'/articleInfo/'.$item->id.'?inwehub_user_device=wechat';
                }
            }
            $upvote = Support::where('user_id',$user->id)
                ->where('supportable_id',$item->id)
                ->where('supportable_type',Submission::class)
                ->exists();
            $tags = $item->tags()->select('tags.id','tags.name')->get()->toArray();
            if ($item->isRecommendRead()) {
                $tags[] = [
                    'id' => -1,
                    'name'=>'推荐'
                ];
            }
            $img = $item->data['img']??'';
            if (is_array($img)) {
                if ($img) {
                    $img = $img[0];
                } else {
                    $img = '';
                }
            }
            $list[] = [
                'id'    => $item->id,
                'title' => strip_tags($item->data['title']??$item->title),
                'type'  => $item->type,
                'domain'    => $domain,
                'img'   => $img,
                'slug'      => $item->slug,
                'category_id' => $item->category_id,
                'is_upvoted'     => $upvote ? 1 : 0,
                'link_url'  => $link_url,
                'rate'  => (int)(substr($item->rate,8)?:0),
                'comment_number' => $item->comments_number,
                'support_number' => $item->upvotes,
                'share_number' => $item->share_number,
                'tags' => $tags,
                'created_at'=> (string)$item->created_at
            ];
        }
        return self::createJsonData(true,$list);
    }

    //精选推荐
    public function recommendRead(Request $request, JWTAuth $JWTAuth) {
        $perPage = $request->input('perPage',Config::get('inwehub.api_data_page_size'));
        $orderBy = $request->input('orderBy',1);
        $page = $request->input('page',1);
        $alertMsg = '';
        $last_seen= '';
        $recommendType = $request->input('recommendType',1);
        $query = RecommendRead::where('audit_status',1);
        try {
            $user = $JWTAuth->parseToken()->authenticate();
            $last_seen = RateLimiter::instance()->hGet('user_recommend_last_seen',$user->id);
            //按领域推荐
            if ($recommendType == 2) {
                $filterTag = $request->input('tagFilter','');
                if ($filterTag) {
                    $query = $query->whereHas('tags',function($query) use ($filterTag) {
                        $query->where('tag_id', $filterTag);
                    });
                } else {
                    $tags = $user->userRegionTag()->pluck('tag_id')->toArray();
                    if ($tags) {
                        $query = $query->whereHas('tags',function($query) use ($tags) {
                            $query->whereIn('tag_id', $tags);
                        });
                        /*$query = $query->where(function ($query) use ($tags) {
                            $query->whereHas('tags',function($query) use ($tags) {
                                $query->whereIn('tag_id', $tags);
                            })->orDoesntHave('tags');
                        });*/
                    }
                }
            }
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        if ($recommendType == 2) {
            $this->doing($user,Doing::ACTION_VIEW_SKILL_DOMAIN,'',0,'核心页面');
        }

        switch ($orderBy) {
            case 1:
                //热门
                $query = $query->orderBy('rate','desc')->orderBy('id','desc');
                break;
            case 2:
                //随机
                $count = $query->count();
                $rand = Config::get('inwehub.api_data_page_size')/$count * 100;
                $query = $query->where(DB::raw('RAND()'),'<=',$rand)->distinct()->orderBy(DB::raw('RAND()'));
                break;
            case 3:
                //发布时间
                $query = $query->orderBy('id','desc');
                break;
        }
        if ($page == 1 && $recommendType == 1) {
            if ($last_seen) {
                $ids = $query->take(100)->pluck('id')->toArray();
                $newCount = array_search($last_seen,$ids);
                if ($newCount === false) {
                    $newCount = '99+';
                }
                if ($newCount) {
                    $alertMsg = '更新了'.$newCount.'条信息';
                } else {
                    $alertMsg = '暂无新信息';
                }
            } else {
                $alertMsg = '已为您更新';
            }
        }
        $reads = $query->simplePaginate($perPage);
        $result = $reads->toArray();
        foreach ($result['data'] as $key=>&$item) {
            if ($page == 1 && $key == 0) {
                $last_seen = $item['id'];
            }
            $item = $this->formatRecommendReadItem($item);
        }
        if ($page == 1 && $recommendType == 1) {
            RateLimiter::instance()->hSet('user_recommend_last_seen',$user->id,$last_seen);
        }
        $result['alert_msg'] = $alertMsg;
        return self::createJsonData(true, $result);
    }

    public function myCommentList(Request $request, JWTAuth $JWTAuth){
        $uuid = $request->input('uuid');
        try{
            $loginUser = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $loginUser = new \stdClass();
            $loginUser->id = 0;
            $loginUser->name = '游客';
            $loginUser->uuid = 0;
        }
        if ($uuid) {
            $user = User::where('uuid',$uuid)->first();
            if (!$user) {
                throw new ApiException(ApiException::BAD_REQUEST);
            }
        } else {
            $user = $request->user();
        }
        $comments = $user->comments()->orderBy('id','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = [];
        $origin_title = '';
        $comment_url = '';
        $type = 1;
        $slug = '';
        foreach ($comments as $comment) {
            $continue = false;
            switch ($comment->source_type) {
                case 'App\Models\Article':
                    $source = $comment->source;
                    $origin_title = '活动:'.$source->title;
                    $comment_url = '/EnrollmentStatus/'.$source->id;
                    $continue = true;
                    continue;
                    break;
                case 'App\Models\Answer':
                    $source = $comment->source;
                    $question = $source->question;
                    if ($question->question_type == 1) {
                        $origin_title = '问答:'.$question->title;
                        $comment_url = '/askCommunity/major/'.$source->question_id;
                    } else {
                        $origin_title = '问答:'.$question->title;
                        $comment_url = '/askCommunity/interaction/'.$source->id;
                    }
                    $continue = true;
                    continue;
                    break;
                case 'App\Models\Readhub\Comment':
                    $continue = true;
                    continue;
                    $type = 2;
                    $readhub_comment = Comment::find($comment->source_id);
                    $submission = Submission::find($readhub_comment->submission_id);
                    if (!$submission) continue;
                    $origin_title = '文章:'.$submission->title;
                    $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                    break;
                case 'App\Models\Submission':
                    $type = 2;
                    $submission = Submission::find($comment->source_id);
                    if (!$submission) {
                        $continue = true;
                        continue;
                    }
                    $origin_title = ($submission->type == 'review'?'点评:':'动态:').$submission->formatTitle();
                    $comment_url = '/c/'.$submission->category_id.'/'.$submission->slug;
                    $slug = $submission->slug;
                    if ($submission->type == 'review') {
                        $comment_url = '/dianping/comment/'.$submission->slug;
                    } elseif ($submission->group_id && $user->uuid != $loginUser->uuid && !$submission->group->public) {
                        $continue = true;
                        continue;
                    }
                    break;
            }
            if (!$continue) {
                $return[] = [
                    'id' => $comment->id,
                    'type'    => $type,
                    'content' => $comment->formatContent(),
                    'slug' => $slug,
                    'origin_title' => $origin_title,
                    'comment_url'  => $comment_url,
                    'created_at' => date('Y/m/d H:i',strtotime($comment->created_at))
                ];
            }
        }
        $list = $comments->toArray();
        $list['data'] = $return;
        return self::createJsonData(true,  $list);
    }

}