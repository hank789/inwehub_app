<?php namespace App\Traits;
/**
 * @author: wanghui
 * @date: 2017/4/7 下午1:32
 * @email: hank.huiwang@gmail.com
 */
use App\Events\Frontend\Answer\Answered;
use App\Events\Frontend\System\ImportantNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\LogUserViewTags;
use App\Jobs\NewSubmissionJob;
use App\Jobs\SaveActivity;
use App\Jobs\UpdateSubmissionRate;
use App\Jobs\UploadFile;
use App\Logic\QuestionLogic;
use App\Logic\QuillLogic;
use App\Logic\TaskLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Company\CompanyData;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\DownVote;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\Notification;
use App\Models\PartnerOauth;
use App\Models\Pay\Settlement;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Submission;
use App\Models\Support;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Models\Taggable;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionAnswered;
use App\Notifications\NewQuestionConfirm;
use App\Services\Hmac\Server;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\HeaderBag;
use Zhuzhichao\IpLocationZh\Ip;
use App\Events\Frontend\System\Credit as CreditEvent;
use Illuminate\Http\Request;

trait BaseController {

    protected function findIp($ip): array
    {
        return (array) Ip::find($ip);
    }

    /**
     * 修改用户积分
     * @param $user_id; 用户id
     * @param $action;  执行动作：提问、回答、发起文章
     * @param int $source_id; 源：问题id、回答id、文章id等
     * @param string $source_subject; 源主题：问题标题、文章标题等
     * @param bool $toSlack
     * @return bool;           操作成功返回true 否则  false
     */
    protected function credit($user_id,$action,$source_id = 0 ,$source_subject = null, $toSlack = true)
    {
        event(new CreditEvent($user_id,$action,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$source_id,$source_subject,$toSlack));
    }

    protected function calculationSubmissionRate($submissionId){
        $event = 'calculation:submission:rate';
        $limit = RateLimiter::instance()->getValue($event,$submissionId);
        if (!$limit) {
            RateLimiter::instance()->increase($event,$submissionId,10,1);
            dispatch(new UpdateSubmissionRate($submissionId))->delay(Carbon::now()->addSeconds(10));
        }
    }

    protected function checkCommentIsSupported($user_id, &$comment) {
        $support = Support::where("user_id",'=',$user_id)->where('supportable_type','=',Comment::class)->where('supportable_id','=',$comment['id'])->first();
        $comment['is_supported'] = $support?1:0;
        if ($comment['children']) {
            foreach ($comment['children'] as &$children) {
                $this->checkCommentIsSupported($user_id, $children);
            }
        } else {
            return;
        }
    }


    protected function creditAccountInfoCompletePercent($uid,$percent){
        $valid_percent = config('inwehub.user_info_valid_percent',90);
        $count = 0;
        if ($percent >= $valid_percent) {
            $count = Redis::connection()->incr('inwehub:account_info_complete_credit:'.$uid);
        }
        $user = User::find($uid);
        $sendNotice = false;
        if ($percent >= 30 && $percent <= 80) {
            $sendNotice = true;
        }

        if ($count == 1){
            $this->credit($uid,Credit::KEY_USER_INFO_COMPLETE,$uid,'简历完成');
            $sendNotice = true;
        }
        if ($count >= 1) {
            TaskLogic::finishTask('newbie_complete_userinfo',0,'newbie_complete_userinfo',[$uid]);
        }
        if ($sendNotice) {
            if(!RateLimiter::instance()->increase('send:system:notice',$uid,50,1)){
                event(new SystemNotify('用户'.$user->id.'['.$user->name.']简历完成了'.$percent.';从业时间['.$user->getWorkYears().']年'));
            }
        }
        $user->info_complete_percent = $percent;
        $user->save();
    }

    /**
     * 记录用户动态
     * @param $user; 动态发起人
     * @param $action;  动作 ['ask','answer',...]
     * @param $source_id; 问题或文章ID
     * @param $subject;   问题或文章标题
     * @param string $content; 回答或评论内容
     * @param int $refer_id;  问题或者文章ID
     * @param int $refer_user_id; 引用内容作者ID
     * @param null $refer_content; 引用内容
     * @param string $link
     * @return static
     */
    protected function doing($user,$action,$source_type,$source_id,$subject,$content='',$refer_id=0,$refer_user_id=0,$refer_content='',$link = '')
    {
        $ua = app('request')->header('user-agent');
        if (searchKeys($ua,['Googlebot','Baiduspider','Sogou spider','Sogou web spider','MSNBot'],100)) {
            return;
        }
        if (strpos($action,'view') === 0 || strpos($action,'share') === 0) {
            $slackFields = [];
            if ($link) {
                $slackFields[] = [
                    'title'=>'链接',
                    'value'=>$link
                ];
            }
            $from  = Input::get('inwehub_user_device');
            if (strpos($action,'share') === 0) {
                event(new ImportantNotify('['.$from.']用户'.$user->id.'['.$user->name.']'.Doing::$actionName[$action].($subject?':'.str_limit(strip_tags($subject)):''),$slackFields));
            } elseif ($user->id>0) {
                event(new SystemNotify('['.$from.']用户'.$user->id.'['.$user->name.']'.Doing::$actionName[$action].($subject?':'.str_limit(strip_tags($subject)):''),$slackFields));
            }
        }
        if($user->id && RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('doing_'.$action,$user->id.'_'.$source_id)){
            try {
                dispatch(new SaveActivity([
                    'user_id' => $user->id,
                    'action' => $action,
                    'source_id' => $source_id,
                    'source_type' => $source_type,
                    'subject' => '',
                    'content' => '',
                    'refer_id' => $refer_id,
                    'refer_user_id' => $refer_user_id,
                    'refer_content' => '',
                    'created_at' => date('Y-m-d H:i:s')
                ]));
            } catch (\Exception $e) {
                app('sentry')->captureException($e);
            }
        }
    }

    protected function logUserViewTags($user_id,$tags) {
        if(RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('log_user_view_tags',$user_id,30)){
            if ($user_id > 0) {
                UserTag::multiIncrement($user_id,$tags,'views');
            }
        }
    }


    /**
     * 创建任务
     * @param $user_id
     * @param $source_type
     * @param $source_id
     * @param $action
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function task($user_id,$source_type,$source_id,$action){
        return TaskLogic::task($user_id,$source_type,$source_id,$action);
    }

    protected function finishTask($source_type,$source_id,$action,$user_ids,$expert_user_ids=[]){
        return TaskLogic::finishTask($source_type,$source_id,$action,$user_ids,$expert_user_ids);
    }


    /**
     * 发送用户通知
     * @param $from_user_id
     * @param $to_user_id
     * @param $type
     * @param $subject
     * @param $source_id
     * @return static
     */
    protected function notify($from_user_id,$to_user_id,$type,$subject='',$source_id=0,$content='',$refer_type='',$refer_id=0)
    {
        return;
        /*不能自己给自己发通知*/
        if( $from_user_id == $to_user_id ){
            return false;
        }

        $toUser = User::find($to_user_id);

        if( !$toUser ){
            return false;
        }
        /*站内消息策略*/
        if(!in_array($type,explode(",",$toUser->site_notifications))){
            return false;
        }

        return Notification::create([
            'user_id'    => $from_user_id,
            'to_user_id' => $to_user_id,
            'type'       => $type,
            'subject'    => strip_tags($subject),
            'source_id'    => $source_id,
            'content'  => $content,
            'refer_type'  => $refer_type,
            'refer_id'  => $refer_id,
            'is_read'    => 0
        ]);


    }


    /**
     * 将通知设置为已读
     * @param $source_id
     * @param string $refer_type
     * @return mixed
     */
    protected function readNotifications($source_id,$refer_type='question')
    {
        return;
        $types = [];
        if($refer_type=='article'){
            $types = ['comment_article'];
        }else if($refer_type=='question'){
            $types = ['answer','follow_question','comment_question','invite_answer','adopt_answer'];
        }else if($refer_type=='answer'){
            $types = ['comment_answer'];
        }else if($refer_type == 'user'){
            $types = ['follow_user'];
        }
        $types[] = 'reply_comment';
        return Notification::where('to_user_id','=',Auth()->user()->id)->where('source_id','=',$source_id)->whereIn('type',$types)->where('is_read','=',0)->update(['is_read'=>1]);
    }


    /*邮件发送*/
    protected function sendEmail($email,$subject,$message){

        if(Setting()->get('mail_open') != 1){//关闭邮件发送
            return false;
        }

        $data = [
            'email' => $email,
            'subject' => $subject,
            'body' => $message,
        ];


        Mail::queue('emails.common', $data, function($message) use ($data)
        {
            $message->to($data['email'])->subject($data['subject']);
        });

    }

    /**
     * 业务层计数器
     * @param $key 计数器key
     * @param null $step 级数步子
     * @param int $expiration 有效期
     * @return Int count
     */
    protected function counter($key,$step=null,$expiration=86400){

        $count = Cache::get($key,0);
        /*直接获取值*/
        if( $step === null ){
            return $count;
        }

        $count = $count + $step;

        Cache::put($key,$count,$expiration);

        return $count;
    }

    protected function uploadImgs($photos,$dir='submissions'){
        $list = [];
        if ($photos) {
            if (!is_array($photos)) $photos = [$photos];
            foreach ($photos as $base64) {
                $url = explode(';',$base64);
                if(count($url) <=1){
                    $parse_url = parse_url($base64);
                    //非本地地址，存储到本地
                    if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                        $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                        dispatch((new UploadFile($file_name,base64_encode(file_get_contents_curl($base64)))));
                        //Storage::disk('oss')->put($file_name,file_get_contents($base64));
                        $img_url = Storage::disk('oss')->url($file_name);
                        $list[] = $img_url;
                    } elseif(isset($parse_url['host'])) {
                        $list[] = $base64;
                    }
                    continue;
                }
                $url_type = explode('/',$url[0]);
                $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                //Storage::disk('oss')->put($file_name,base64_decode(substr($url[1],6)));
                $img_url = Storage::disk('oss')->url($file_name);
                $list[] = $img_url;
            }
        }
        return ['img'=>$list];
    }

    protected function uploadFile($files,$dir='submissions'){
        $list = [];
        if ($files) {
            foreach ($files as $file) {
                $url = explode(';',$file['base64']);
                if(count($url) <=1){
                    continue;
                }
                $url_type = explode('/',$url[0]);
                $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                $img_url = Storage::disk('oss')->url($file_name);
                $list[] = [
                    'name' => $file['name'],
                    'type' => $url_type[1],
                    'url' =>$img_url
                ];
            }
        }
        return $list;
    }

    protected function storeAnswer(User $loginUser, $description, Request $request) {
        if(RateLimiter::instance()->increase('question:answer:create',$loginUser->id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $question_id = $request->input('question_id');
        $question = Question::find($question_id);

        if(empty($question)){
            throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
        }

        $lock_key = 'question_answer_action';
        $doing_prefix = '';

        $question_invitation = QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$loginUser->id)->first();
        if ($question->question_type == 1) {
            if(empty($question_invitation)){
                throw new ApiException(ApiException::ASK_QUESTION_NOT_EXIST);
            }

            RateLimiter::instance()->lock_acquire($lock_key,1,20);
            if($question_invitation->status == QuestionInvitation::STATUS_ANSWERED){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_ANSWERED);
            }
            //检查问题是否已经被其它人回答
            $exit_answers = Answer::where('question_id',$question_id)->whereIn('status',[1,3])->where('user_id','!=',$loginUser->id)->get()->last();
            if($exit_answers){
                RateLimiter::instance()->lock_release($lock_key);
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_CONFIRMED);
            }
        }

        if ($question->question_type == 2) {
            //互动问答只能回答一次
            $exit_answers = Answer::where('question_id',$question_id)->where('user_id',$loginUser->id)->get()->last();
            if ($exit_answers){
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_ANSWERED);
            }
        }

        $promise_time = $request->input('promise_time');

        if(empty($promise_time) && strlen(trim($description)) <= 4){
            throw new ApiException(ApiException::ASK_ANSWER_CONTENT_TOO_SHORT);
        }

        $answerContent = QuillLogic::parseImages($description);
        if ($answerContent === false){
            $answerContent = $description;
        }

        $data = [
            'user_id'      => $loginUser->id,
            'question_id'      => $question_id,
            'content'  => $answerContent,
            'status'   => Answer::ANSWER_STATUS_FINISH,
            'device'       => intval($request->input('device'))
        ];

        if ($question->question_type == 1) {
            //付费专业问答
            //先检查是否已有回答
            $answer = Answer::where('question_id',$question_id)->where('user_id',$loginUser->id)->get()->last();

            if(!$answer){
                if($promise_time){
                    if(strlen($promise_time) != 4) {
                        throw new ApiException(ApiException::ASK_ANSWER_PROMISE_TIME_INVALID);
                    }
                    $hours = substr($promise_time,0,2);
                    $minutes = substr($promise_time,2,2);
                    $data['promise_time'] = date('Y-m-d H:i:00',strtotime('+ '.$hours.' hours + '.$minutes.' minutes'));
                    $data['status'] = Answer::ANSWER_STATUS_PROMISE;
                    $data['content'] = '承诺在:'.$data['promise_time'].'前回答该问题';
                }else{
                    $data['adopted_at'] = date('Y-m-d H:i:s');
                    $data['status'] = Answer::ANSWER_STATUS_FINISH;
                }
                $answer = Answer::create($data);
            }elseif($promise_time){
                //重复响应
                throw new ApiException(ApiException::ASK_QUESTION_ALREADY_SELF_CONFIRMED);
            }
        } else {
            $doing_prefix = 'free_';
            //互助问答
            $answer = Answer::create($data);
        }


        if($answer){
            if(empty($promise_time)){
                /*用户回答数+1*/
                $loginUser->userData()->increment('answers');

                /*问题回答数+1*/
                $question->increment('answers');

                //问题变为已回答
                $question->answered();

                if ($question->question_type == 1) {
                    $answer->status = Answer::ANSWER_STATUS_FINISH;
                    $answer->content = $answerContent;
                    $answer->adopted_at = date('Y-m-d H:i:s');
                    $answer->save();

                    $this->task($question->user_id,get_class($answer),$answer->id,Task::ACTION_TYPE_ANSWER_FEEDBACK);
                }

                //任务变为已完成
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[]);

                UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'answers');
                UserTag::multiIncrement($loginUser->id,$question->tags()->get(),'questions');

                /*记录动态*/
                $this->doing($answer->user,$doing_prefix.'question_answered',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);

                /*记录通知*/
                if ($question->user_id != $answer->user_id)
                    $question->user->notify(new NewQuestionAnswered($question->user_id,$question,$answer));

                /*回答后通知关注问题*/
                if(true){
                    $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($question))->where('source_id','=',$question->id)->count();
                    if($attention===0){
                        $data = [
                            'user_id'     => $loginUser->id,
                            'source_id'   => $question->id,
                            'source_type' => get_class($question),
                            'subject'  => $question->title,
                        ];
                        Attention::create($data);

                        $question->increment('followers');
                    }
                }
                /*修改问题邀请表的回答状态*/
                QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$loginUser->id)->update(['status'=>QuestionInvitation::STATUS_ANSWERED]);
                RateLimiter::instance()->lock_release($lock_key);

                $this->counter( 'answer_num_'. $answer->user_id , 1 , 3600 );
                $message = '回答成功!';

                //首次回答额外积分
                if($loginUser->userData->answers == 1)
                {
                    if ($question->question_type == 1) {
                        $credit_key = Credit::KEY_FIRST_ANSWER;
                    } else {
                        $credit_key = Credit::KEY_FIRST_COMMUNITY_ANSWER;
                    }
                } else {
                    if ($question->question_type == 1) {
                        $credit_key = Credit::KEY_ANSWER;
                    } else {
                        $credit_key = Credit::KEY_COMMUNITY_ANSWER;
                    }
                }

                $this->credit($loginUser->id,$credit_key,$answer->id,$answer->getContentText());

                //匿名提问提问者不加分
                if ($question->question_type == 2 && $question->hide == 0) {
                    $this->credit($question->user_id,Credit::KEY_COMMUNITY_ASK_ANSWERED,$answer->id,$answer->getContentText());
                }

                event(new Answered($answer));
                if ($question->question_type == 1) {
                    //进入结算中心
                    Settlement::answerSettlement($answer);
                    Settlement::questionSettlement($question);
                }
                QuestionLogic::calculationQuestionRate($answer->question_id);
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at],ApiException::SUCCESS,$message);

            }else{
                //问题变为待回答
                $question->confirmedAnswer();
                $this->finishTask(get_class($question),$question->id,Task::ACTION_TYPE_ANSWER,[],[$loginUser->id]);
                /*修改问题邀请表的回答状态*/
                QuestionInvitation::where('question_id','=',$question->id)->where('user_id','=',$loginUser->id)->update(['status'=>QuestionInvitation::STATUS_CONFIRMED]);
                /*记录动态*/
                $this->doing($answer->user,'question_answer_confirmed',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);
                RateLimiter::instance()->lock_release($lock_key);
                event(new Answered($answer));
                $question->user->notify(new NewQuestionConfirm($question->user_id,$question,$answer));
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
            }
        }

        throw new ApiException(ApiException::ERROR);
    }

    protected function getTagProductInfo(Tag $tag) {
        $reviewInfo = Tag::getReviewInfo($tag->id);
        $data = $tag->toArray();
        $data['review_count'] = $reviewInfo['review_count'];
        $data['review_average_rate'] = $reviewInfo['review_average_rate'];
        $submissions = Submission::selectRaw('count(*) as total,rate_star')->where('status',1)->where('category_id',$tag->id)->groupBy('rate_star')->get();
        foreach ($submissions as $submission) {
            $data['review_rate_info'][] = [
                'rate_star' => $submission->rate_star,
                'count'=> $submission->total
            ];
        }

        $data['related_tags'] = $tag->relationReviews(4);
        $categoryRels = TagCategoryRel::where('tag_id',$tag->id)->where('type',TagCategoryRel::TYPE_REVIEW)->orderBy('review_average_rate','desc')->get();
        $cids = [];
        foreach ($categoryRels as $key=>$categoryRel) {
            $cids[] = $categoryRel->category_id;
            $category = Category::find($categoryRel->category_id);
            $rate = TagCategoryRel::where('category_id',$category->id)->where('review_average_rate','>',$categoryRel->review_average_rate)->count();
            $data['categories'][] = [
                'id' => $category->id,
                'name' => $category->name,
                'rate' => $rate+1,
                'support_rate' => $categoryRel->support_rate?:0,
                'type' => $category->type == 'enterprise_review'?1:2
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
        /*$releatedTags = TagCategoryRel::whereIn('category_id',$cids)->pluck('tag_id')->toArray();
        $recommendUsers = UserTag::whereIn('tag_id',$releatedTags)->where('user_id','!=',$user->id)->orderBy('skills','desc')->take(5)->get();
        $skillTags = TagsLogic::loadTags(5,'')['tags'];
        foreach ($recommendUsers as $recommendUser) {
            $userTags = UserTag::where('user_id',$recommendUser->user_id)->whereIn('tag_id',array_column($skillTags,'value'))->orderBy('skills','desc')->pluck('tag_id');
            if (!isset($userTags[0])) continue;
            $skillTag = Tag::find($userTags[0]);
            if (!$skillTag) continue;
            $data['recommend_users'][] = [
                'name' => $recommendUser->user->name,
                'id'   => $recommendUser->user_id,
                'uuid' => $recommendUser->user->uuid,
                'is_expert' => $recommendUser->user->is_expert,
                'avatar_url' => $recommendUser->user->avatar,
                'skill' => $skillTag->name
            ];
        }*/
        return $data;
    }

    protected function formatSubmissionInfo(Request $request,Submission $submission, $user) {
        $return = $submission->toArray();
        if ($submission->group_id) {
            $group = Group::find($submission->group_id);
            $return['group'] = $group->toArray();
            $return['group']['is_joined'] = 1;
            $return['group']['name'] = str_limit($return['group']['name'], 20);
            if ($group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
                $groupMember = GroupMember::where('user_id',$user->id)->where('group_id',$group->id)->first();
                $return['group']['is_joined'] = -1;
                if ($groupMember) {
                    $return['group']['is_joined'] = $groupMember->audit_status;
                }
                if ($user->id == $group->user_id) {
                    $return['group']['is_joined'] = 3;
                }
                //$return['group']['subscribers'] = $group->getHotIndex();

                if ($group->public == 0 && in_array($return['group']['is_joined'],[-1,0,2]) ) {
                    //私有圈子
                    return $return;
                }
            } else {
                $return['group']['subscribers'] = $group->getHotIndex() + User::count();
            }
        }
        if ($request->input('inwehub_user_device') == 'www' && $return['type'] == 'article' && !str_contains($request->header('Referer'),'my/discover/add/'.$submission->slug)) {
            $return['data']['description'] = QuillLogic::parseHtml($return['data']['description']);
        }
        $this->dispatch(new LogUserViewTags($user->id,$submission));
        $this->calculationSubmissionRate($submission->id);

        $upvote = Support::where('user_id',$user->id)
            ->where('supportable_id',$submission->id)
            ->where('supportable_type',Submission::class)
            ->exists();
        $downvote = DownVote::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
            ->exists();
        $bookmark = Collection::where('user_id',$user->id)
            ->where('source_id',$submission->id)
            ->where('source_type',Submission::class)
            ->exists();

        $attention_user = Attention::where("user_id",'=',$user->id)->where('source_id','=',$submission->user_id)->where('source_type','=',get_class($user))->first();
        $return['is_followed_author'] = $attention_user ?1 :0;
        $return['is_upvoted'] = $upvote ? 1 : 0;
        $return['is_downvoted'] = $downvote ? 1 : 0;
        $return['is_bookmark'] = $bookmark ? 1: 0;
        $return['supporter_list'] = [];
        $return['support_description'] = $downvote?$submission->getDownvoteRateDesc():$submission->getSupportRateDesc($upvote);
        $return += $submission->getSupportTypeTip();
        $return['support_percent'] = $submission->getSupportPercent();
        $return['tags'] = $submission->tags()->wherePivot('is_display',1)->get()->toArray();
        foreach ($return['tags'] as $key=>$tag) {
            $return['tags'][$key]['review_average_rate'] = 0;
            if (isset($submission->data['category_ids'])) {
                $reviewInfo = Tag::getReviewInfo($tag['id']);
                $return['tags'][$key]['reviews'] = $reviewInfo['review_count'];
                $return['tags'][$key]['review_average_rate'] = $reviewInfo['review_average_rate'];
            }
        }
        $return['is_commented'] = $submission->comments()->where('user_id',$user->id)->exists() ? 1: 0;
        $return['bookmarks'] = Collection::where('source_id',$submission->id)
            ->where('source_type',Submission::class)->count();
        $return['data']['current_address_name'] = $return['data']['current_address_name']??'';
        $return['data']['current_address_longitude'] = $return['data']['current_address_longitude']??'';
        $return['data']['current_address_latitude']  = $return['data']['current_address_latitude']??'';
        $img = $return['data']['img']??'';
        if (false && in_array($return['group']['is_joined'],[-1,0,2]) && $img) {
            if (is_array($img)) {
                foreach ($img as &$item) {
                    $item .= '?x-oss-process=image/blur,r_20,s_20';
                }
            } else {
                $img .= '?x-oss-process=image/blur,r_20,s_20';
            }
        }
        $return['data']['img'] = $img;
        $return['related_question'] = null;
        if (isset($return['data']['related_question']) && $return['data']['related_question']) {
            $related_question = Question::find($return['data']['related_question']);
            $answer_uids = Answer::where('question_id',$related_question->id)->take(3)->pluck('user_id')->toArray();
            $answer_users = [];
            foreach ($answer_uids as $answer_uid) {
                $answer_user = User::find($answer_uid);
                $answer_users[] = [
                    'uuid' => $answer_user->uuid,
                    'avatar' => $answer_user->avatar
                ];
            }
            $return['related_question'] = [
                'id' => $related_question->id,
                'question_type' => $related_question->question_type,
                'price'      => $related_question->price,
                'title'  => $related_question->title,
                'tags' => $related_question->tags()->wherePivot('is_display',1)->get()->toArray(),
                'status' => $related_question->status,
                'status_description' => $related_question->price.'元',
                'follow_number' => $related_question->followers,
                'answer_number' => $related_question->answers,
                'answer_users'  => $answer_users
            ];
        }

        if ($submission->hide) {
            //匿名
            $return['owner']['avatar'] = config('image.user_default_avatar');
            $return['owner']['name'] = '匿名';
            $return['owner']['id'] = '';
            $return['owner']['uuid'] = '';
            $return['owner']['is_expert'] = 0;
        }
        $return['related_tags'] = $submission->getRelatedProducts();
        //seo信息
        $keywords = array_unique(explode(',',$submission->data['keywords']??''));
        $return['seo'] = [
            'title' => strip_tags($submission->type == 'link' ? $submission->data['title'] : $submission->title),
            'description' => strip_tags($submission->title),
            'keywords' => implode(',',array_slice($keywords,0,5)),
            'published_time' => (new Carbon($submission->created_at))->toAtomString()
        ];
        return $return;
    }

    protected function storeSubmission(Request $request,$user) {
        $user_id = $user->id;
        if (RateLimiter::instance()->increase('submission:store',$user_id,5)) {
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        if ($request->type == 'link') {
            $category = Category::where('slug','channel_xwdt')->first();
        } else {
            $category = Category::where('slug','channel_gddj')->first();
        }
        $category_id = $category->id;

        $tagString = $request->input('tags');
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
        $group_id = $request->input('group_id',0);
        $public = 1;
        $hide = $request->input('hide',0);
        if ($request->type != 'review' && $group_id) {
            $group = Group::find($group_id);
            if ($group->audit_status != Group::AUDIT_STATUS_SYSTEM) {
                if ($group->audit_status != Group::AUDIT_STATUS_SUCCESS) {
                    throw new ApiException(ApiException::GROUP_UNDER_AUDIT);
                }
                $groupMember = GroupMember::where('user_id',$user_id)->where('group_id',$group_id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();
                if (!$groupMember) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
            }
            $public = $group->public;
        }

        //点评
        if ($request->type == 'review') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'tags' => 'required',
                'rate_star' => 'required|min:1',
                'identity' => 'required'
            ]);
            $data = $this->uploadImgs($request->input('photos'));
            $data['category_ids'] = $request->input('category_ids',[]);
            $data['author_identity'] = $request->input('identity');
            $data['from_source'] = $request->input('inwehub_user_device');
            if (!is_array($data['author_identity'])) {
                $data['author_identity'] = [$data['author_identity']];
            }
            if ($request->input('hide',0) && $user->isRole('operator')) {
                //点评运营人员
                $data['real_author'] = $user->id;
                $role = Role::where('slug','dianpingrobot')->first();
                $roleUsers = RoleUser::where('role_id',$role->id)->pluck('user_id')->toArray();
                $user_id = array_random($roleUsers);
                $hide = 0;
            }
            $category_id = $tagString;
        }

        if ($request->type == 'article') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'description' => 'required',
                'group_id' => 'required|integer'
            ]);
            $description = QuillLogic::parseImages($request->input('description'));
            if ($description === false){
                $description = $request->input('description');
            }
            $img_url = $this->uploadImgs($request->input('photos'));
            $data = [
                'description'   => $description,
                'img'           => $img_url['img']?$img_url['img'][0]:''
            ];
        }

        if ($request->type == 'link') {
            $this->validate($request, [
                'url'   => 'required|url',
                'title' => 'required|between:1,6000',
            ]);

            //检查url是否重复
            $exist_submission_id = Redis::connection()->hget('voten:submission:url',$request->url);
            if ($exist_submission_id && empty($group_id)){
                $exist_submission = Submission::find($exist_submission_id);
                if (!$exist_submission) {
                    throw new ApiException(ApiException::ARTICLE_URL_ALREADY_EXIST);
                }
                $exist_submission_url = '/c/'.$exist_submission->category_id.'/'.$exist_submission->slug;
                return self::createJsonData(false,['exist_url'=>$exist_submission_url],ApiException::ARTICLE_URL_ALREADY_EXIST,"您的分享已存在，前往为ta点个赞吧！");
            }
            try {
                $img_url = $this->uploadImgs($request->input('photos'));

                $data = [
                    'url'           => $request->url,
                    'title'         => Cache::get('url_title_'.$request->url,''),
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => ($img_url['img']?$img_url['img'][0]:'')?:Cache::get('url_img_'.$request->url,''),
                    'thumbnail'     => null,
                    'providerName'  => null,
                    'publishedTime' => null,
                    'domain'        => domain($request->url),
                ];
                Redis::connection()->hset('voten:submission:url',$request->url,1);
            } catch (\Exception $e) {
                $data = [
                    'url'           => $request->url,
                    'title'         => $request->title,
                    'description'   => null,
                    'type'          => 'link',
                    'embed'         => null,
                    'img'           => null,
                    'thumbnail'     => null,
                    'providerName'  => null,
                    'publishedTime' => null,
                    'domain'        => domain($request->url),
                ];
            }
        }

        if ($request->type == 'text') {
            $this->validate($request, [
                'title' => 'required|between:1,6000',
                'group_id' => 'required|integer'
            ]);

            $data = $this->uploadImgs($request->input('photos'));
        }
        if ($request->input('files')) {
            $data['files'] = $this->uploadFile($request->input('files'));
        }

        try {
            $data['current_address_name'] = $request->input('current_address_name');
            $data['current_address_longitude'] = $request->input('current_address_longitude');
            $data['current_address_latitude'] = $request->input('current_address_latitude');
            $data['mentions'] = is_array($request->input('mentions'))?array_unique($request->input('mentions')):[];
            $title = formatHtml($request->title);
            $submission = Submission::create([
                'title'         => formatContentUrls($title),
                'slug'          => $this->slug($title),
                'type'          => $request->type,
                'category_id'   => $category_id,
                'group_id'      => $group_id,
                'public'        => $public,
                'rate'          => firstRate(),
                'rate_star'     => $request->input('rate_star',0),
                'hide'          => $hide,
                'status'        => $request->input('draft',0)?0:1,
                'user_id'       => $user_id,
                'data'          => $data,
                'views'         => 1
            ]);
            if ($request->type == 'link') {
                Redis::connection()->hset('voten:submission:url',$request->url, $submission->id);
            }

            /*添加标签*/
            Tag::multiSaveByIds($tagString,$submission);
            if ($newTagString) {
                Tag::multiAddByName($newTagString,$submission);
            }
            UserTag::multiIncrement($user_id,$submission->tags()->get(),'articles');
            if ($request->input('identity') && $request->input('identity') != -1) {
                UserTag::multiIncrement($user_id,[Tag::find($request->input('identity'))],'role');
            }
            if ($submission->status == 1) {
                $this->dispatch((new NewSubmissionJob($submission->id,false,$request->input('inwehub_user_device')== 'weapp_dianping'?'小程序':'')));
            }

        } catch (\Exception $exception) {
            app('sentry')->captureException($exception);
            throw new ApiException(ApiException::ERROR);
        }
        self::$needRefresh = true;
        return self::createJsonData(true,$submission->toArray());
    }

    protected function tagProductList(Request $request) {
        $category_id = $request->input('category_id',0);
        $orderBy = $request->input('orderBy',1);
        $page = $request->input('page',1);
        $cacheKey = 'tags:product_list_'.$category_id.'_'.$orderBy.'_'.$page;
        $preCacheKey = '';
        if ($page > 1) {
            $preCacheKey = 'tags:product_list_'.$category_id.'_'.$orderBy.'_'.($page-1);
        }
        $return = Cache::get($cacheKey);
        if (!$return) {
            $query = TagCategoryRel::select(['tag_id'])->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1);
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
            $tags = $query->distinct()->groupBy('tag_id')->simplePaginate(30);
            $return = $tags->toArray();
            $list = [];
            $used = [];
            $preCache = $preCacheKey?Cache::get($preCacheKey):'';
            if ($preCache) {
                $used = array_column($preCache['data'],'id');
            }
            foreach ($tags as $tag) {
                if (in_array($tag->tag_id, $used)) continue;
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
            $return['data'] = $list;
            Cache::forever($cacheKey,$return);
        }
        return $return;
    }

    protected function validPartnerOauth(Request $request) {
        $app_id = $request->input('auth_key');
        $oauth = PartnerOauth::where('app_id',$app_id)->where('status',1)->first();
        if (!$oauth) {
            throw new ApiException(ApiException::TOKEN_INVALID);
        }
        $res = Server::instance()->validate($request->all());
        if ($res['code'] != 1000) {
            throw new ApiException(ApiException::BAD_REQUEST);
        }
    }

}