<?php namespace App\Api\Controllers\Account;

use App\Cache\UserCache;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Logic\TagsLogic;
use App\Logic\WithdrawLogic;
use App\Models\AddressBook;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Feed\Feed;
use App\Models\Feedback;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use App\Models\ProductUserRel;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\UserTag;
use App\Services\City\CityData;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Api\Controllers\Controller;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class ProfileController extends Controller
{

    /*个人基本资料*/
    public function info(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();
        $data = Cache::get('user_info_'.$user->id);
        $need_report = $request->input('need_report',0);
        if (!$data) {
            $info = [];
            $info['id'] = $user->id;
            $info['uuid'] = $user->uuid;
            $info['name'] = $user->name;
            $info['realname'] = $user->realname;
            $info['current_day_signed'] = RateLimiter::instance()->getValue('sign:'.$user->id,date('Ymd'))?1:0;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
            $info['rc_code'] = $user->rc_code;
            $info['avatar_url'] = $user->avatar;
            $info['gender'] = $user->gender;
            $info['birthday'] = $user->birthday;
            $info['province']['key'] = $user->province;
            $info['province']['name'] = CityData::getProvinceName($user->province)?:$user->province;
            $info['city']['key'] = $user->city;
            $info['city']['name'] = CityData::getCityName($user->province,$user->city)?:$user->city;

            $info['hometown_province']['key'] = $user->hometown_province;
            $info['hometown_province']['name'] = CityData::getProvinceName($user->hometown_province)?:$user->hometown_province;
            $info['hometown_city']['key'] = $user->hometown_city;
            $info['hometown_city']['name'] = CityData::getCityName($user->hometown_province,$user->hometown_city)?:$user->hometown_city;

            $info['company'] = $user->company;
            $info['title'] = $user->title;
            $info['description'] = $user->description;
            $info['status'] = $user->status;
            $info['address_detail'] = $user->address_detail;
            $info['industry_tags'] = TagsLogic::formatTags($user->industryTags());
            if(empty($info['industry_tags'])) $info['industry_tags'] = '';
            $info['skill_tags'] = TagsLogic::formatTags(Tag::whereIn('id',$user->userSkillTag()->pluck('tag_id'))->get());
            $info['region_tags'] = TagsLogic::formatTags(Tag::whereIn('id',$user->userRegionTag()->pluck('tag_id'))->get());
            $info['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
            $info['expert_level'] = $info['is_expert'] === 1 ? $user->authentication->getLevelName():'';
            $info['is_company'] = $user->userData->is_company;
            $info['company_status'] = $user->userCompany->apply_status??0;
            $info['show_my_wallet'] = true;
            if (in_array($user->id,[504])) {
                $info['show_my_wallet'] = false;
            }
            $info['show_ios_resume'] = true;
            if(config('app.env') == 'production'){
                //ios正在审核,暂时不显示个人名片
                $info['show_ios_resume'] = true;
            }

            $info['expert_apply_status'] = 0;
            $info['expert_apply_tips'] = '点击前往认证';
            if(!empty($user->authentication)){
                if($user->authentication->status == 0){
                    $info['expert_apply_status'] = 1;
                    $info['expert_apply_tips'] = '认证处理中!';
                }elseif($user->authentication->status == 1){
                    $info['expert_apply_status'] = 2;
                    $info['expert_apply_tips'] = '身份已认证!';
                }else{
                    $info['expert_apply_status'] = 3;
                    $info['expert_apply_tips'] = '认证失败,重新认证';
                }
            }

            $info['followers'] = $user->attentions()->count();
            $info['followed_number'] = $user->followers()->count();
            $info['popularity'] = Doing::where('action',Doing::ACTION_VIEW_RESUME)->where('source_id',$user->id)->where('user_id','!=',$user->id)->count();
            $info['publishes'] = $user->userData->questions
                + $user->userData->answers + Submission::where('user_id',$user->id)->count()
                + Comment::where('user_id',$user->id)->count();
            $info['collections'] = $user->collections()->count();
            $groupIds = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->pluck('id')->toArray();
            $info['groups'] = GroupMember::where('user_id',$user->id)->whereIn('group_id',$groupIds)->count();
            $info['feedbacks'] = Feedback::where('to_user_id',$user->id)->count();
            $info['total_score'] = '综合评分暂无';

            $info['submission_karma'] = $user->submission_karma;
            $info['comment_karma'] = $user->comment_karma;
            $info['is_admin'] = $user->isRole('operatormanager');


            $info_percent = $user->getInfoCompletePercent(true);
            $info['account_info_complete_percent'] = $info_percent['score'];

            $infos = '';
            if($info_percent['unfilled']){
                $infos = '请完善'.User::getFieldHumanName($info_percent['unfilled'][0]).(count($info_percent['unfilled'])>1?'等':'').'信息';
            }


            $info['account_info_valid_percent'] = config('inwehub.user_info_valid_percent');
            $info['total_money'] = 0;
            $user_money = UserMoney::find($user->id);
            if($user_money){
                $info['total_money'] = $user_money->total_money;
            }
            $info['questions'] = $user->userData->questions;
            $info['answers'] = $user->userData->answers;
            //加上承诺待回答的
            $info['answers'] += Answer::where('user_id',$user->id)->where('status',3)->count();
            $info['tasks'] = $user->tasks->where('status',0)->count();
            $info['projects'] = $user->companyProjects->where('status','!=',0)->count();
            $info['user_level'] = $user->userData->user_level;
            $info['user_credits'] = $user->userData->credits;
            $info['user_coins'] = $user->userData->coins;
            $info['my_activity_enroll'] = Collection::where('user_id',$user->id)->where('source_type','App\Models\Article')->count();

            $info['newbie_unfinish_tasks']= ['readhub_comment'=>false,'ask'=>false,'complete_userinfo'=>false,'show_guide'=>true];
            $newbie_readhub_comment_task = Task::where('user_id',$user->id)->where('source_type','newbie_readhub_comment')->where('status',1)->first();
            if ($newbie_readhub_comment_task) {
                $info['newbie_unfinish_tasks']['readhub_comment'] = true;
            }
            $newbie_ask_task = Task::where('user_id',$user->id)->where('source_type','newbie_ask')->where('status',1)->first();
            if ($newbie_ask_task) {
                $info['newbie_unfinish_tasks']['ask'] = true;
            }

            $newbie_complete_userinfo_task = Task::where('user_id',$user->id)->where('source_type','newbie_complete_userinfo')->where('status',1)->first();
            if ($newbie_complete_userinfo_task) {
                $info['newbie_unfinish_tasks']['complete_userinfo'] = true;
            }
            if ($user->attentions()->count()>=1 || $info['region_tags'] || $info['groups']) {
                $info['newbie_unfinish_tasks']['show_guide'] = false;
            }

            $jobs = $user->jobs()->orderBy('begin_time','desc')->pluck('company');
            $job_desc = '';
            if($jobs->count()){
                $job_desc = $jobs[0].($jobs->count()>1?'等':'').$jobs->count().'个工作';
            }

            $projects = $user->projects()->orderBy('begin_time','desc')->pluck('project_name');
            $project_desc = '';
            if($projects->count()){
                $project_desc = $projects[0].($projects->count()>1?'等':'').$projects->count().'个项目';
            }

            $edus = $user->edus()->orderBy('begin_time','desc')->pluck('school');
            $edu_desc = '';
            if ($edus->count()) {
                $edu_desc = $edus[0].($edus->count()>1?'等':'').$edus->count().'所学校';
            }

            $trains = $user->trains()->orderBy('get_time','desc')->pluck('certificate');
            $train_desc = '';
            if ($trains->count()) {
                $train_desc = $trains[0].($trains->count()>1?'等':'').$trains->count().'个认证';
            }

            $data = [
                'info'   => $info,
                'infos'  => $infos,
                'jobs'   => $job_desc,
                'projects' => $project_desc,
                'edus'   => $edu_desc,
                'trains'  => $train_desc
            ];
            Cache::forever('user_info_'.$user->id,$data);
        }
        if ($need_report) {
            $this->doing($user,Doing::ACTION_VIEW_MY_INFO,'',0,'核心页面');
        }
        $data['productManager'] = false;
        $managerPros = ProductUserRel::where('user_id',$user->id)->where('status',1)->count();
        if ($managerPros > 0) {
            $data['productManager'] = true;
        }

        return self::createJsonData(true,$data,ApiException::SUCCESS,'ok');
    }


    public function infoByUuid(Request $request,JWTAuth $JWTAuth) {
        $validateRules = [
            'uuid' => 'required|min:10',
        ];
        $this->validate($request,$validateRules);
        $uuid = $request->input('uuid');
        $user = User::where('uuid',$uuid)->first();
        if (empty($user)) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $jwtToken = $JWTAuth->getToken();
        $loginUser = '';
        if($jwtToken){
            try{
                $loginUser = $JWTAuth->toUser($JWTAuth->getToken());
            } catch (\Exception $e){

            }
        }
        $is_self = false;
        $is_followed = 0;
        if($loginUser && $loginUser->id == $user->id){
            $is_self = true;
        }elseif($loginUser) {
            $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($user))->where('source_id','=',$user->id)->first();
            if ($attention){
                $is_followed = 1;
            }
        }

        $info = [];
        $info['id'] = $user->id;
        $info['uuid'] = $user->uuid;
        $info['name'] = $user->name;
        $info['realname'] = $is_self?$user->realname:'';
        $info['mobile'] = $user->mobile;
        $info['email'] = $user->email;
        $info['avatar_url'] = $user->avatar;
        $info['gender'] = $user->gender;
        $info['birthday'] = $user->birthday;
        $info['province']['key'] = $user->province;
        $info['province']['name'] = CityData::getProvinceName($user->province)?:$user->province;
        $info['city']['key'] = $user->city;
        $info['city']['name'] = CityData::getCityName($user->province,$user->city)?:$user->city;

        $info['hometown_province']['key'] = $user->hometown_province;
        $info['hometown_province']['name'] = CityData::getProvinceName($user->hometown_province)?:$user->hometown_province;
        $info['hometown_city']['key'] = $user->hometown_city;
        $info['hometown_city']['name'] = CityData::getCityName($user->hometown_province,$user->hometown_city)?:$user->hometown_city;

        $info['company'] = $user->company;
        $info['title'] = $user->title;
        $info['description'] = $user->description;
        $info['status'] = $user->status;
        $info['address_detail'] = $user->address_detail;
        $info['industry_tags'] = TagsLogic::formatTags($user->industryTags());
        if(empty($info['industry_tags'])) $info['industry_tags'] = '';
        $info['skill_tags'] = TagsLogic::formatTags(Tag::whereIn('id',$user->userSkillTag()->pluck('tag_id'))->get());
        $info['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
        $info['expert_level'] = $info['is_expert'] === 1 ? $user->authentication->getLevelName():'';

        $data = [
            'info'   => $info,
            'is_followed' => $is_followed,
        ];

        return self::createJsonData(true,$data);
    }

    //用户个人名片
    public function resumeInfo(Request $request,JWTAuth $JWTAuth){
        $validateRules = [
            'uuid' => 'required|min:10',
        ];
        $this->validate($request,$validateRules);
        $uuid = $request->input('uuid');
        $user = User::where('uuid',$uuid)->first();
        if (empty($user)) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $jwtToken = $JWTAuth->getToken();
        $loginUser = '';
        $loginUserInfoCompletePercent = 0;
        if($jwtToken){
            try{
                $loginUser = $JWTAuth->toUser($JWTAuth->getToken());
                if ($loginUser->id != $user->id) {
                    $this->doing($loginUser,Doing::ACTION_VIEW_RESUME,get_class($user),$user->id,$user->name,'',0,0,'',config('app.mobile_url').'#/share/resume?id='.$user->uuid);
                }
                $info_percent = $loginUser->getInfoCompletePercent(true);
                $loginUserInfoCompletePercent = $info_percent['score'];
            } catch (\Exception $e){

            }
        }
        $is_self = false;
        $is_followed = 0;
        if($loginUser && $loginUser->id == $user->id){
            $is_self = true;
        }elseif($loginUser) {
            $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($user))->where('source_id','=',$user->id)->first();
            if ($attention){
                $is_followed = 1;
            }
        }

        $info = [];
        $info['id'] = $user->id;
        $info['uuid'] = $user->uuid;
        $info['name'] = $user->name;
        $info['realname'] = $is_self?$user->realname:'';
        $info['mobile'] = $user->mobile;
        $info['email'] = $user->email;
        $info['avatar_url'] = $user->avatar;
        $info['gender'] = $user->gender;
        $info['birthday'] = $user->birthday;
        $info['province']['key'] = $user->province;
        $info['province']['name'] = CityData::getProvinceName($user->province)?:$user->province;
        $info['city']['key'] = $user->city;
        $info['city']['name'] = CityData::getCityName($user->province,$user->city)?:$user->city;

        $info['hometown_province']['key'] = $user->hometown_province;
        $info['hometown_province']['name'] = CityData::getProvinceName($user->hometown_province)?:$user->hometown_province;
        $info['hometown_city']['key'] = $user->hometown_city;
        $info['hometown_city']['name'] = CityData::getCityName($user->hometown_province,$user->hometown_city)?:$user->hometown_city;

        $info['company'] = $user->company;
        $info['title'] = $user->title;
        $info['description'] = $user->description;
        $info['status'] = $user->status;
        $info['address_detail'] = $user->address_detail;
        $info['industry_tags'] = TagsLogic::formatTags($user->industryTags());
        if(empty($info['industry_tags'])) $info['industry_tags'] = '';
        $info['skill_tags'] = TagsLogic::formatTags(Tag::whereIn('id',$user->userSkillTag()->pluck('tag_id'))->get());
        $info['is_expert'] = ($user->authentication && $user->authentication->status === 1) ? 1 : 0;
        $info['expert_level'] = $info['is_expert'] === 1 ? $user->authentication->getLevelName():'';
        $info['expert_apply_status'] = 0;
        $info['expert_apply_tips'] = '点击前往认证';
        if(!empty($user->authentication)){
            if($user->authentication->status == 0){
                $info['expert_apply_status'] = 1;
                $info['expert_apply_tips'] = '认证处理中!';
            }elseif($user->authentication->status == 1){
                $info['expert_apply_status'] = 2;
                $info['expert_apply_tips'] = '身份已认证!';
            }else{
                $info['expert_apply_status'] = 3;
                $info['expert_apply_tips'] = '认证失败,重新认证';
            }
        }

        $info['questions'] = $is_self?$user->userData->questions:($user->questions->where('question_type',1)->where('is_recommend',1)->where('hide',0)->count() + $user->questions->where('question_type',2)->where('hide',0)->count());
        $info['answers'] = $user->userData->answers;
        $authSupport = Submission::where('status',1)->where('author_id',$user->id)->sum('upvotes');
        $info['supports'] = $user->answers->sum('supports') + $user->submissions->sum('upvotes') + $authSupport;
        //加上承诺待回答的
        $info['answers'] += Answer::where('user_id',$user->id)->where('status',3)->count();
        $info['projects'] = $user->companyProjects->count();
        $info['user_level'] = $user->userData->user_level;
        $info['is_job_info_public'] = $user->userData->job_public;
        $info['is_project_info_public'] = $user->userData->project_public;
        $info['is_edu_info_public'] = $user->userData->edu_public;
        $info['total_score'] = '暂无';
        $info['work_years'] = $user->getWorkYears();
        $info['followers'] = $user->followers()->count();
        $info['followed_number'] = $user->followers()->count();
        $info['follow_user_number'] = $user->attentions()->where('source_type',User::class)->count();
        $info['feedbacks'] = Feedback::where('to_user_id',$user->id)->count();

        $info['submission_count'] = Submission::where('status',1)->where('user_id',$user->id)->where('public',1)->where('hide',0)->whereNull('deleted_at')->count();
        $info['comment_count'] = Comment::where('user_id',$user->id)->count();
        $info['feed_count'] = Feed::where('user_id',$user->id)->where('is_anonymous',0)->where('feed_type','!=',Feed::FEED_TYPE_FOLLOW_USER)->count();
        $info['article_count'] = Submission::where('status',1)->where('author_id',$user->id)->whereIn('type',['link','article'])->whereNull('deleted_at')->count();
        $info['article_comment_count'] = Submission::where('status',1)->where('author_id',$user->id)->whereIn('type',['link','article'])->whereNull('deleted_at')->sum('comments_number');
        $info['article_upvote_count'] = Submission::where('status',1)->where('author_id',$user->id)->whereIn('type',['link','article'])->whereNull('deleted_at')->sum('upvotes');

        $info['publishes'] = $info['answers'] + $info['questions'] + $info['submission_count'] + $info['comment_count'];
        $groupIds = Group::where('audit_status',Group::AUDIT_STATUS_SUCCESS)->pluck('id')->toArray();
        $info['group_number'] = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->whereIn('group_id',$groupIds)->count();
        $projects = [];
        $jobs = [];
        $edus = [];

        if(($info['is_job_info_public'] && $loginUserInfoCompletePercent >= 90) || $is_self){
            $jobs = $user->jobs()->orderBy('begin_time','desc')->get();
            $jobs = $jobs->toArray();
        }

        if(($info['is_project_info_public'] && $loginUserInfoCompletePercent >= 90) || $is_self){
            $projects = $user->projects()->orderBy('begin_time','desc')->get();

            foreach($projects as &$project){
                $project->industry_tags = '';
                $project->product_tags = '';

                $project->industry_tags = TagsLogic::formatTags($project->tags()->where('category_id',9)->get());
                $project->product_tags = TagsLogic::formatTags($project->tags()->where('category_id',10)->get());
            }
            $projects = $projects->toArray();
        }

        if(($info['is_edu_info_public'] && $loginUserInfoCompletePercent >= 90) || $is_self){
            $edus = $user->edus()->orderBy('begin_time','desc')->get();
            $edus = $edus->toArray();
        }
        $groupMembers = GroupMember::where('user_id',$user->id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->orderBy('id','desc')->get();
        $groups = [];
        foreach ($groupMembers as $groupMember) {
            $group = $groupMember->group;
            if (!$group) continue;
            $groups[] = [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'logo' => $group->logo,
                'public' => $group->public,
                'subscribers' => $group->subscribers,
                'articles'    => $group->articles
            ];
        }

        $data = [
            'info'   => $info,
            'is_followed' => $is_followed,
            'jobs'   => $jobs,
            'projects' => $projects,
            'edus'   => $edus,
            'groups' => $groups
        ];
        return self::createJsonData(true,$data,ApiException::SUCCESS,'ok');

    }

    //修改用户资料
    public function update(Request $request){
        $validateRules = [
            'name' => 'max:128',
            'realname' => 'max:128',
            'gender'    => 'nullable|in:0,1,2',
            'company'   => 'max:128',
            'province' => 'max:128',
            'city'     => 'max:128',
            'hometown_province' => 'max:128',
            'hometown_city'     => 'max:128',
            'address_detail'  => 'max:255',
            'email'            => 'nullable|email',
            'birthday'         => 'max:128',
            'title' => 'max:255'
        ];
        $user = $request->user();
        $validateRules['email'] = 'nullable|email|max:255|unique:users,email,'.$user->id;
        $this->validate($request,$validateRules);
        $notifyInfo = '用户'.$user->id.'['.$user->name.']';
        $notify = false;
        if($request->input('name') !== null){
            if ($request->input('name') != $user->name) {
                $notifyInfo .= '昵称变更为['.$request->input('name').'];';
                $notify = true;
            }
            $user->name = $request->input('name');
        }

        if($request->input('realname') !== null){
            if ($request->input('realname') != $user->realname) {
                $notifyInfo .= '真实姓名变更为['.$request->input('realname').'];';
                $notify = true;
            }
            $user->realname = $request->input('realname');
        }

        if($request->input('email') !== null){
            $user->email = strtolower($request->input('email'));
        }

        if($request->input('gender') !== null){
            $user->gender = $request->input('gender');
        }

        if($request->input('birthday') !== null){
            $user->birthday = $request->input('birthday');
        }

        if($request->input('title') !== null){
            if ($user->title != $request->input('title')) {
                $notifyInfo .= '职位['.$user->title.']变更为['.$request->input('title').'];';
                $notify = true;
            }
            $user->title = $request->input('title');
        }

        if($request->input('company') !== null){
            if ($user->company != $request->input('company')) {
                $notifyInfo .= '公司['.$user->company.']变更为['.$request->input('company').'];';
                $notify = true;
            }
            $user->company = $request->input('company');
        }

        if($request->input('description') !== null){
            $user->description = $request->input('description');
        }

        if($request->input('province') !== null){
            $user->province = $request->input('province');
        }

        if($request->input('city') !== null){
            $user->city = $request->input('city');
        }

        if($request->input('hometown_province') !== null){
            $user->hometown_province = $request->input('hometown_province');
        }

        if($request->input('hometown_city') !== null){
            $user->hometown_city = $request->input('hometown_city');
        }

        if($request->input('address_detail') !== null){
            $user->address_detail = $request->input('address_detail');
        }

        $user->save();
        if ($notify) {
            event(new SystemNotify($notifyInfo));
        }
        if($request->input('industry_tags') !== null){
            $industry_tags = $request->input('industry_tags');
            $tags = Tag::whereIn('id',$industry_tags)->get();
            UserTag::detachByField($user->id,'industries');
            UserTag::multiIncrement($user->id,$tags,'industries');
        }
        UserCache::delUserInfoCache($user->id);

        $percent = $user->getInfoCompletePercent();
        $this->creditAccountInfoCompletePercent($user->id,$percent);
        self::$needRefresh = true;
        return self::createJsonData(true,['account_info_complete_percent'=>$percent]);
    }


    //添加用户擅长标签
    public function addSkillTag(Request $request) {
        $user = $request->user();
        if(RateLimiter::instance()->increase('user:add:skill:tags',$user->id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $newTagString = $request->input('new_tags');
        if ($newTagString) {
            if (is_array($newTagString)) {
                foreach ($newTagString as $s) {
                    if (strlen($s) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
                }
            } else {
                if (strlen($newTagString) > 46) throw new ApiException(ApiException::TAGS_NAME_LENGTH_LIMIT);
            }
        }
        $tagids = $request->input('tags');
        if ($newTagString) {
            $tagids = array_merge($tagids,Tag::addByName($newTagString));
        }
        $tags = Tag::whereIn('id',$tagids)->get();

        UserTag::multiDetachByField($user->id,Tag::whereIn('id',$user->userSkillTag()->pluck('tag_id'))->get(),'skills');
        UserTag::multiIncrement($user->id,$tags,'skills');
        UserCache::delUserInfoCache($user->id);
        self::$needRefresh = true;
        return self::createJsonData(true);
    }

    //添加用户领域标签
    public function updateRegionTag(Request $request) {
        $user = $request->user();
        if(RateLimiter::instance()->increase('user:add:region:tags',$user->id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $tagids = $request->input('tags');

        $tags = Tag::whereIn('id',$tagids)->get();

        UserTag::multiDetachByField($user->id,Tag::whereIn('id',$user->userRegionTag()->pluck('tag_id'))->get(),'region');
        UserTag::multiIncrement($user->id,$tags,'region');
        $fields = [];
        $fields[] = [
            'title'=>'标签',
            'value'=>implode(',',array_column($tags->toArray(),'name'))
        ];
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']添加了领域标签',$fields));
        UserCache::delUserInfoCache($user->id);
        self::$needRefresh = true;
        return self::createJsonData(true);
    }

    //删除用户擅长标签
    public function delSkillTag(Request $request) {
        $validateRules = [
            'tags' => 'required'
        ];
        $user = $request->user();
        $this->validate($request,$validateRules);
        $tagids = $request->input('tags');
        $tags = Tag::whereIn('id',$tagids)->get();
        UserTag::multiDetachByField($user->id,$tags,'skills');
        self::$needRefresh = true;
        UserCache::delUserInfoCache($user->id);
        return self::createJsonData(true);
    }

    /**
     * 修改用户头像
     * @param Request $request
     */
    public function postAvatar(Request $request)
    {
        $validateRules = [
            'user_avatar' => 'required',
        ];
        $this->validate($request,$validateRules);

        $user_id = $request->user()->id;
        if($request->hasFile('user_avatar')){
            $file = $request->file('user_avatar');
            $extension = strtolower($file->getClientOriginalExtension());
            $extArray = array('png', 'gif', 'jpeg', 'jpg');
            if(in_array($extension, $extArray)){
                $request->user()->addMediaFromRequest('user_avatar')->setFileName(User::getAvatarFileName($user_id,'origin').'.'.$extension)->toMediaCollection('avatar');
                $upload_count = Redis::connection()->incr('user_avatar_upload:'.$user_id);
            }else{
                return self::createJsonData(false,[],ApiException::BAD_REQUEST,'头像上传失败');
            }
        }else {
            $request->user()->addMediaFromBase64($request->input('user_avatar'))->toMediaCollection('avatar');
            $upload_count = Redis::connection()->incr('user_avatar_upload:'.$user_id);
        }

        UserCache::delUserInfoCache($user_id);
        if($upload_count == 1){
            //只有首次上传头像才加积分
            $this->credit($user_id,Credit::KEY_UPLOAD_AVATAR,$user_id,'头像上传成功');
        }
        $percent = $request->user()->getInfoCompletePercent();
        $this->creditAccountInfoCompletePercent($user_id,$percent);
        $user = $request->user();
        $user->avatar = $user->getAvatarUrl();
        $user->save();
        self::$needRefresh = true;
        return self::createJsonData(true,['user_avatar_url'=>$user->avatar,'account_info_complete_percent'=>$percent],ApiException::SUCCESS,'上传成功');
    }

    /**
     * 修改用户密码
     * @param Request $request
     */
    public function updatePassword(Request $request)
    {
        $validateRules = [
            'old_password' => 'required|min:6|max:16',
            'password' => 'required|min:6|max:16',
            'password_confirmation'=>'same:password',
        ];
        $this->validate($request,$validateRules);

        $user = $request->user();

        if(Hash::check($request->input('old_password'),$user->password)){
            $user->password = Hash::make($request->input('password'));
            $user->save();
            Auth()->logout();
            return self::createJsonData(true,[],ApiException::SUCCESS,'密码修改成功,请重新登录');
        }

        return self::createJsonData(false,[],ApiException::USER_PASSWORD_ERROR,'原始密码错误');
    }

    public function moneyLog(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = $request->user()->moneyLogs();
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $logs = $query->orderBy('id','DESC')->paginate(Config::get('inwehub.api_data_page_size'));
        $list = [];
        foreach($logs as $log){
            $title = '';
            switch($log->money_type){
                case MoneyLog::MONEY_TYPE_ANSWER:
                    $title = '专业回答收入';
                    break;
                case MoneyLog::MONEY_TYPE_ASK:
                    $title = '付费问答';
                    break;
                case MoneyLog::MONEY_TYPE_FEE:
                    $title = '手续费';
                    break;
                case MoneyLog::MONEY_TYPE_WITHDRAW:
                    $title = '提现';
                    break;
                case MoneyLog::MONEY_TYPE_PAY_FOR_VIEW_ANSWER:
                    $title = '付费围观';
                    break;
                case MoneyLog::MONEY_TYPE_REWARD:
                    $title = '分红';
                    break;
                case MoneyLog::MONEY_TYPE_COUPON:
                    $title = '红包';
                    break;
                case MoneyLog::MONEY_TYPE_ASK_PAY_WALLET:
                    $title = '余额支付';
                    break;
                case MoneyLog::MONEY_TYPE_SYSTEM_ADD:
                    $title = $log->source_type;
                    break;
            }
            $list[] = [
                "id"=> $log->id,
                "user_id" => $log->user_id,
                "change_money" => $log->change_money,
                "io"=> $log->io,
                "title"=> $title,
                "status" => $log->status,
                "created_at" => (string)$log->created_at
            ];
        }

        return self::createJsonData(true,$list);
    }

    public function wallet(Request $request){
        $data = get_pay_config();
        /**
         * @var User
         */
        $user = $request->user();

        $data['total_money'] = 0;
        $data['pay_settlement_money'] = 0;
        $data['is_bind_weixin'] = 0;
        $data['bind_weixin_nickname'] = '';
        $data['user_phone'] = secret_mobile($user->mobile);
        $user_oauth = UserOauth::where('user_id',$user->id)->whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH])->where('status',1)->orderBy('updated_at','desc')->first();
        if($user_oauth){
            $data['is_bind_weixin'] = 1;
            $data['bind_weixin_nickname'] = $user_oauth['nickname'];
        }

        $user_money = UserMoney::find($user->id);
        if($user_money){
            $data['total_money'] = $user_money->total_money;
            $data['pay_settlement_money'] = $user_money->settlement_money;
        }
        $data['withdraw_day_remain'] = $data['withdraw_day_limit'];
        $withdraw_used = WithdrawLogic::getUserWithdrawCount($user->id,'wx_transfer');
        if($withdraw_used){
            $data['withdraw_day_remain'] -= $withdraw_used;
        }

        return self::createJsonData(true,$data);
    }


    public function privacyInfo(Request $request){
        $user = $request->user();
        $return = [
            'is_job_info_public' => $user->userData->job_public,
            'is_project_info_public' => $user->userData->project_public,
            'is_edu_info_public' => $user->userData->edu_public,
            'is_phone_public' => $user->userData->phone_public,
        ];
        return self::createJsonData(true,$return);
    }

    public function privacyUpdate(Request $request){
        $validateRules = [
            'is_edu_info_public' => 'integer',
            'is_project_info_public' => 'integer',
            'is_job_info_public' => 'integer',
            'is_phone_public' => 'integer'
        ];
        $user = $request->user();
        $this->validate($request,$validateRules);
        $userData = $user->userData;
        $message = '设置成功';
        if($request->input('is_edu_info_public') !== null){
            $userData->edu_public = $request->input('is_edu_info_public');
            $message = $request->input('is_edu_info_public') ? '教育经历公开':'教育经历仅自己可见';
        }

        if($request->input('is_project_info_public') !== null){
            $userData->project_public = $request->input('is_project_info_public');
            $message = $request->input('is_project_info_public') ? '项目经历公开':'项目经历仅自己可见';
        }

        if($request->input('is_job_info_public') !== null){
            $userData->job_public = $request->input('is_job_info_public');
            $message = $request->input('is_job_info_public') ? '工作经历公开':'工作经历仅自己可见';
        }

        if($request->input('is_phone_public') !== null){
            $userData->phone_public = $request->input('is_phone_public');
        }
        $userData->save();

        return self::createJsonData(true,[
            'is_edu_info_public'=> $userData->edu_public,
            'is_project_info_public' => $userData->project_public,
            'is_job_info_public'     => $userData->job_public
        ],ApiException::SUCCESS,$message);
    }


    //上传简历
    public function uploadResume(Request $request){
        $validateRules = [
            'user_resume_1'  => 'required|image',
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;

        if(RateLimiter::instance()->increase('user:profile:upload:resume',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $count = $this->counter('user:resume:upload:'.date('Y-m-d').':'.$user_id,1);
        if ($count >= 2) {
            throw new ApiException(ApiException::USER_RESUME_UPLOAD_LIMIT);
        }

        for($i=1;$i<=5;$i++){
            $name = 'user_resume_'.$i;
            if($request->hasFile($name)){
                $file_0 = $request->file($name);
                $extension = strtolower($file_0->getClientOriginalExtension());
                $extArray = array('png', 'gif', 'jpeg', 'jpg');
                if(in_array($extension, $extArray)){
                    $request->user()->addMediaFromRequest($name)->setFileName(time().'_'.md5($file_0->getFilename()).'.'.$extension)->toMediaCollection('resume');
                }else{
                    return self::createJsonData(false,[],ApiException::BAD_REQUEST,'格式错误');
                }
            }
        }

        return self::createJsonData(true);
    }


    //最近访客
    public function recentVisitors(Request $request) {
        $validateRules = [
            'uuid' => 'required|min:10'
        ];
        $this->validate($request,$validateRules);
        $uuid = $request->input('uuid');
        $page = $request->input('page',1);
        $user = User::where('uuid',$uuid)->first();
        if (empty($user)) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $loginUser = $request->user();
        if ($loginUser->userData->user_level < 4 && $page >= 2) {
            //throw new ApiException(ApiException::USER_LEVEL_LIMIT);
        }
        $doings = Doing::where('action',Doing::ACTION_VIEW_RESUME)->where('source_id',$user->id)->where('user_id','!=',$user->id)->orderBy('id','desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $return = $doings->toArray();
        $list = [];
        foreach ($doings as $doing) {
            $list[] = [
                'id' => $doing->id,
                'user_id' => $doing->user_id,
                'uuid'    => $doing->user->uuid,
                'user_name' => $doing->user->name,
                'is_expert' => $doing->user->is_expert,
                'user_avatar_url' => $doing->user->avatar,
                'description'     => $doing->user->description,
                'visited_time'    => timestamp_format($doing->created_at)
            ];
        }
        $return['data'] = $list;
        //人气
        $hot = $doings->total();
        $return['hot_number'] = $hot;
        return self::createJsonData(true,$return);
    }

    //保存用户通讯录
    public function saveAddressBook(Request $request) {
        $validateRules = [
            'contacts' => 'required|array'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $contacts = $request->input('contacts');
        foreach ($contacts as $contact) {
            //{"id":2097,"rawId":null,"target":0,"displayName":"李柏林","name":null,"nickname":null,"phoneNumbers":[{"value":"13606268446","pref":false,"id":0,"type":"mobile"}],"emails":null,"addresses":null,"ims":null,"organizations":null,"birthday":null,"note":null,"photos":null,"categories":null,"urls":null}
            if (empty($contact['phoneNumbers'])) continue;
            $addressBook = AddressBook::where('user_id',$user->id)->where('address_book_id',$contact['id'])->first();
            $phone = formatAddressBookPhone($contact['phoneNumbers'][0]['value']);
            $display_name = $contact['displayName']?:$phone;
            if ($addressBook) {
                $addressBook->display_name = $display_name;
                $addressBook->phone = $phone;
                $addressBook->detail = $contact;
                $addressBook->save();
            } else {
                AddressBook::create([
                    'user_id' => $user->id,
                    'address_book_id' => $contact['id'],
                    'display_name' => $display_name,
                    'phone'   => $phone,
                    'detail'  => $contact,
                    'status'  => 1
                ]);
            }
        }
        Cache::delete('user_address_book_list_'.$user->id);
        Cache::put('user_sync_address_book_list_'.$user->id,1,60*24*3);
        self::$needRefresh = true;
        return self::createJsonData(true);
    }

    public function needAddressBookRefresh(Request $request) {
        $user = $request->user();
        $refresh = 0;
        if (!Cache::get('user_sync_address_book_list_'.$user->id)) {
            $refresh = 1;
        }
        return self::createJsonData(true,['refresh'=>$refresh]);

    }

    //用户通讯录列表
    public function addressBookList(Request $request) {
        $user = $request->user();
        $cache = Cache::get('user_address_book_list_'.$user->id);
        if (!$cache) {
            $addressBooks = AddressBook::where('user_id',$user->id)->where('status',1)->get()->toArray();
            $appUsers = [];
            $notAppUsers = [];
            foreach ($addressBooks as $addressBook) {
                $addressBook['is_app_user'] = 0;
                foreach ($addressBook['detail']['phoneNumbers'] as $phoneItem) {
                    $phoneUser = User::where('mobile',formatAddressBookPhone($phoneItem['value']))->first();
                    if ($phoneUser) {
                        //过滤掉自己
                        if ($phoneUser->id == $user->id) continue;
                        if (!$phoneUser->userData->phone_public) continue;
                        $addressBook['is_app_user'] = 1;
                        $addressBook['app_user_name'] = $phoneUser->name;
                        $addressBook['app_user_avatar'] = $phoneUser->avatar;
                        $attention = Attention::where("user_id",'=',$user->id)->where('source_type','=',get_class($phoneUser))->where('source_id','=',$phoneUser->id)->first();
                        $addressBook['app_user_is_followed'] = 0;
                        $addressBook['app_user_uuid'] = $phoneUser->uuid;
                        if ($attention) {
                            $addressBook['app_user_is_followed'] = 1;
                            $attention2 = Attention::where("user_id",'=',$phoneUser->id)->where('source_type','=',get_class($phoneUser))->where('source_id','=',$user->id)->first();
                            if ($attention2) {
                                $addressBook['app_user_is_followed'] = 2;
                            }
                        }
                        break;
                    }
                }
                unset($addressBook['detail']);
                unset($addressBook['address_book_id']);
                unset($addressBook['phone']);
                if ($addressBook['is_app_user']) {
                    $appUsers[] = $addressBook;
                } else {
                    $notAppUsers[] = $addressBook;
                }
            }
            $cache = [
                'appUsers' => $appUsers,
                'notAppUsers' => $notAppUsers,
            ];
            Cache::put('user_address_book_list_'.$user->id, $cache,30);
        }

        return self::createJsonData(true, $cache);
    }

    public function inviteAddressBookUser(Request $request) {
        $validateRules = [
            'id' => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $addressBook = AddressBook::find($request->input('id'));
        if (!$addressBook) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        if (RateLimiter::instance()->increase('send_invite_address_book_user_msg',$addressBook->id,60*5)) {
            throw new ApiException(ApiException::USER_INVITE_ADDRESSBOOK_USER_LIMIT);
        }
        foreach ($addressBook->detail['phoneNumbers'] as $phoneItem) {
            dispatch((new SendPhoneMessage(formatAddressBookPhone($phoneItem['value']),['name' => $user->name],'invite_address_book_user')));
        }
        return self::createJsonData(true,[],ApiException::SUCCESS,'邀请成功');
    }

}
