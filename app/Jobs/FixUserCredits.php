<?php

namespace App\Jobs;

use App\Models\Answer;
use App\Models\Comment;
use App\Models\Credit as CreditModel;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use App\Models\UserData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class FixUserCredits implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $uid;



    public function __construct($uid)
    {
        $this->uid = $uid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->uid);
        //注册积分
        $action = CreditModel::KEY_REGISTER;
        $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
        $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$user,'注册成功');
        //上传头像积分
        $action = CreditModel::KEY_UPLOAD_AVATAR;
        $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $this->credit('',$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$user,'头像上传成功',$reg?$reg->created_at:'');
        //简历完成积分
        $action = CreditModel::KEY_USER_INFO_COMPLETE;
        $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $this->credit('',$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$user,'简历完成',$reg?$reg->created_at:'');
        //完成首次专业提问
        $action = CreditModel::KEY_FIRST_ASK;
        $question = Question::where('user_id',$user->id)->where('question_type',1)->orderBy('id','asc')->first();
        if ($question) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$question,$question->title);
        }
        //完成首次互动提问
        $action = CreditModel::KEY_FIRST_COMMUNITY_ASK;
        $question = Question::where('user_id',$user->id)->where('question_type',2)->orderBy('id','asc')->first();
        if ($question) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$question,$question->title);
        }
        //专业提问
        $action = CreditModel::KEY_ASK;
        $questions = Question::where('user_id',$user->id)->where('question_type',1)->orderBy('id','asc')->get();
        foreach ($questions as $key=>$question) {
            if ($key == 0) continue;
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$question->id)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$question,$question->title);
        }
        //互动提问
        $action = CreditModel::KEY_COMMUNITY_ASK;
        $questions = Question::where('user_id',$user->id)->where('question_type',2)->orderBy('id','asc')->get();
        foreach ($questions as $key=>$question) {
            if ($key == 0) continue;
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$question->id)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$question,$question->title);
        }

        //专业问答回答&互动问答回答
        $answers = Answer::where('user_id',$user->id)->where('status',1)->orderBy('id','asc')->get();
        $first_pay_answer = 0;
        $first_free_answer = 0;
        foreach ($answers as $key=>$answer) {
            $question = $answer->question;
            if ($question->question_type == 1) {
                //专业回答
                $first_pay_answer++;
                $action = CreditModel::KEY_ANSWER;
                if ($first_pay_answer == 1) {
                    //完成首次专业回答
                    $action = CreditModel::KEY_FIRST_ANSWER;
                }
            } else {
                //互动回答
                $first_free_answer++;
                $action = CreditModel::KEY_COMMUNITY_ANSWER;
                if ($first_free_answer == 1) {
                    //完成首次互助回答
                    $action = CreditModel::KEY_FIRST_COMMUNITY_ANSWER;
                }
            }
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$answer->id)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$answer,$answer->getContentText());
        }
        //邀请好友
        $action = CreditModel::KEY_INVITE_USER;
        $rcUsers = User::where('rc_uid',$user->id)->get();
        foreach ($rcUsers as $rcUser) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$rcUser->id)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$rcUser,'邀请好友注册成功');
        }
        //完成专家认证
        $action = CreditModel::KEY_EXPERT_VALID;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        if ($user->authentication && $user->authentication->status === 1){
            $this->credit('',$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$user,'专家认证',$user->authentication->updated_at);
        }
        //阅读回复
        $comments = Comment::where('status',1)->where('user_id',$user->id)->get();
        $action = CreditModel::KEY_READHUB_NEW_COMMENT;
        foreach ($comments as $comment) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$comment->id)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$comment,'回复');
        }
        //阅读发文
        $action = CreditModel::KEY_READHUB_NEW_SUBMISSION;
        $submissions = Submission::where('user_id',$user->id)->get();
        foreach ($submissions as $submission) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$submission->id)->first();
            $this->credit($reg,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$submission,'动态分享');
        }
        //分享成功
        $action = CreditModel::KEY_SHARE_SUCCESS;
        $models = CreditModel::where('user_id',$user->id)->where('action',$action)->get();
        foreach ($models as $model) {
            $this->credit($model,$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),'','');
        }
        //专业问答评价&专业问答围观者评价
        CreditModel::where('user_id',$user->id)->whereIn('action',[CreditModel::KEY_RATE_ANSWER,CreditModel::KEY_FEEDBACK_RATE_ANSWER])->delete();
        $feedbacks = Feedback::where('user_id',$user->id)->where('source_type',Answer::class)->get();
        foreach ($feedbacks as $feedback) {
            $answer = Answer::find($feedback->source_id);
            $question = $answer->question;
            if ($user->id == $question->user_id) {
                //提问者点评
                $action = CreditModel::KEY_RATE_ANSWER;
            } else {
                //围观者点评
                $action = CreditModel::KEY_FEEDBACK_RATE_ANSWER;
            }
            $this->credit('',$action,$user->id,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$feedback,'回答评价');
        }
        $total_coins = CreditModel::where('user_id',$user->id)->sum('coins');
        $total_credits = CreditModel::where('user_id',$user->id)->sum('credits');
        $userData = UserData::find($user->id);
        $userData->coins = $total_coins;
        $userData->credits = $total_credits;
        $userData->save();
    }

    public function credit($creditExist,$action,$user_id,$coins,$credits,$source,$source_subject,$created_at=''){
        try{
            if($coins ==0 && $credits == 0) return false;

            if ($creditExist) {
                if ($creditExist->coins != $coins || $creditExist->credits != $credits) {
                    //修正数据
                    $creditExist->coins = $coins;
                    $creditExist->credits = $credits;
                    $creditExist->save();
                }
            } else {
                CreditModel::create([
                    'user_id' => $user_id,
                    'action' => $action,
                    'source_id' => $source->id,
                    'source_subject' => $source_subject,
                    'coins' => $coins,
                    'credits' => $credits,
                    'current_coins' => 0,
                    'current_credits' => 0,
                    'created_at' => $created_at?:$source->created_at
                ]);
            }
            return true;
        }catch (\Exception $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}
