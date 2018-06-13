<?php namespace App\Traits;
/**
 * @author: wanghui
 * @date: 2017/4/7 下午1:32
 * @email: wanghui@yonglibao.com
 */
use App\Events\Frontend\Answer\Answered;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\SaveActivity;
use App\Jobs\UploadFile;
use App\Logic\QuestionLogic;
use App\Logic\QuillLogic;
use App\Logic\TaskLogic;
use App\Models\Answer;
use App\Models\Attention;
use App\Models\Credit;
use App\Models\Notification;
use App\Models\Pay\Settlement;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Task;
use App\Models\User;
use App\Models\UserTag;
use App\Notifications\NewQuestionAnswered;
use App\Notifications\NewQuestionConfirm;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
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
     * @param $user_id; 动态发起人
     * @param $action;  动作 ['ask','answer',...]
     * @param $source_id; 问题或文章ID
     * @param $subject;   问题或文章标题
     * @param string $content; 回答或评论内容
     * @param int $refer_id;  问题或者文章ID
     * @param int $refer_user_id; 引用内容作者ID
     * @param null $refer_content; 引用内容
     * @return static
     */
    protected function doing($user_id,$action,$source_type,$source_id,$subject,$content='',$refer_id=0,$refer_user_id=0,$refer_content='')
    {
        if(RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('doing_'.$action,$user_id.'_'.$source_id)){
            try {
                dispatch(new SaveActivity([
                    'user_id' => $user_id,
                    'action' => $action,
                    'source_id' => $source_id,
                    'source_type' => $source_type,
                    'subject' => $subject,
                    'content' => $content,
                    'refer_id' => $refer_id,
                    'refer_user_id' => $refer_user_id,
                    'refer_content' => $refer_content,
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
            foreach ($photos as $base64) {
                $url = explode(';',$base64);
                if(count($url) <=1){
                    $parse_url = parse_url($base64);
                    //非本地地址，存储到本地
                    if (isset($parse_url['host']) && !in_array($parse_url['host'],['cdnread.ywhub.com','cdn.inwehub.com','inwehub-pro.oss-cn-zhangjiakou.aliyuncs.com','intervapp-test.oss-cn-zhangjiakou.aliyuncs.com'])) {
                        $file_name = $dir.'/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.jpeg';
                        dispatch((new UploadFile($file_name,base64_encode(file_get_contents($base64)))));
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
                $this->doing($answer->user_id,$doing_prefix.'question_answered',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);

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
                $this->doing($answer->user_id,'question_answer_confirmed',get_class($question),$question->id,$question->title,$answer->getContentText(),$answer->id,$question->user_id);
                RateLimiter::instance()->lock_release($lock_key);
                event(new Answered($answer));
                $question->user->notify(new NewQuestionConfirm($question->user_id,$question,$answer));
                return self::createJsonData(true,['question_id'=>$answer->question_id,'answer_id'=>$answer->id,'create_time'=>(string)$answer->created_at]);
            }
        }

        throw new ApiException(ApiException::ERROR);
    }
}