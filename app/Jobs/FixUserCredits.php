<?php

namespace App\Jobs;

use App\Models\Answer;
use App\Models\Attention;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Company\Company;
use App\Models\Credit as CreditModel;
use App\Models\Doing;
use App\Models\Feedback;
use App\Models\Pay\Order;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use App\Models\UserData;
use App\Services\RateLimiter;
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
        $this->credit($reg,$action,$user->id,$user,'注册成功');
        //上传头像积分
        $action = CreditModel::KEY_UPLOAD_AVATAR;
        $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $this->credit('',$action,$user->id,$user,'头像上传成功',$reg?$reg->created_at:'');
        //简历完成积分
        $action = CreditModel::KEY_USER_INFO_COMPLETE;
        $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $this->credit('',$action,$user->id,$user,'简历完成',$reg?$reg->created_at:'');
        //完成首次专业提问
        $action = CreditModel::KEY_FIRST_ASK;
        $question = Question::where('user_id',$user->id)->where('question_type',1)->orderBy('id','asc')->first();
        if ($question) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
            $this->credit($reg,$action,$user->id,$question,$question->title);
        }
        //完成首次互动提问
        $action = CreditModel::KEY_FIRST_COMMUNITY_ASK;
        $question = Question::where('user_id',$user->id)->where('question_type',2)->orderBy('id','asc')->first();
        if ($question) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->first();
            $this->credit($reg,$action,$user->id,$question,$question->title);
        }
        //专业提问
        $action = CreditModel::KEY_ASK;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $questions = Question::where('user_id',$user->id)->where('question_type',1)->orderBy('id','asc')->get();
        foreach ($questions as $key=>$question) {
            if ($key == 0) continue;
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$question->id)->first();
            $this->credit($reg,$action,$user->id,$question,$question->title);
        }
        //互动提问
        $action = CreditModel::KEY_COMMUNITY_ASK;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $questions = Question::where('user_id',$user->id)->where('question_type',2)->orderBy('id','asc')->get();
        foreach ($questions as $key=>$question) {
            if ($key == 0) continue;
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$question->id)->first();
            $this->credit($reg,$action,$user->id,$question,$question->title);
        }

        //专业问答回答&互动问答回答
        CreditModel::where('user_id',$user->id)->whereIn('action',[CreditModel::KEY_ANSWER,CreditModel::KEY_COMMUNITY_ANSWER])->delete();
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
                $reg1 = CreditModel::where('user_id',$answer->question->user_id)->where('action',CreditModel::KEY_COMMUNITY_ASK_ANSWERED)->where('source_id',$answer->id)->first();
                $this->credit($reg1,CreditModel::KEY_COMMUNITY_ASK_ANSWERED,$answer->question->user_id,$answer,$answer->getContentText());
            }
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$answer->id)->first();
            $this->credit($reg,$action,$user->id,$answer,$answer->getContentText());
        }
        //邀请好友
        $action = CreditModel::KEY_INVITE_USER;
        $rcUsers = User::where('rc_uid',$user->id)->get();
        foreach ($rcUsers as $rcUser) {
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$rcUser->id)->first();
            $this->credit($reg,$action,$user->id,$rcUser,'邀请好友注册成功');
        }
        //完成专家认证
        $action = CreditModel::KEY_EXPERT_VALID;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        if ($user->authentication && $user->authentication->status === 1){
            $this->credit('',$action,$user->id,$user,'专家认证',$user->authentication->updated_at);
        }
        //完成公司认证
        $action = CreditModel::KEY_COMPANY_VALID;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $company = Company::find($user->id);
        if ($company && $company->apply_status == 2){
            $this->credit('',$action,$user->id,$user,'企业认证',$company->updated_at);
        }

        //阅读回复
        $comments = Comment::where('status',1)->where('user_id',$user->id)->get();
        CreditModel::where('user_id',$user->id)->whereIn('action',['readhub_new_comment',CreditModel::KEY_NEW_COMMENT])->delete();
        foreach ($comments as $comment) {
            $source = $comment->source;
            switch ($comment->source_type) {
                case 'App\Models\Article':
                    $action1 = CreditModel::KEY_PRO_OPPORTUNITY_COMMENTED;
                    $source_subject = '项目机遇被回复';
                    break;
                case 'App\Models\Answer':
                    $question = $source->question;
                    if ($question->question_type == 1) {
                        $action1 = CreditModel::KEY_ANSWER_COMMENT;
                        $source_subject = '专业回答被回复';
                    } else {
                        $action1 = CreditModel::KEY_COMMUNITY_ANSWER_COMMENT;
                        $source_subject = '互动回答被回复';
                    }

                    break;
                case 'App\Models\Submission':
                    $action1 = CreditModel::KEY_READHUB_SUBMISSION_COMMENT;
                    $source_subject = '动态分享被回复';
                    break;
            }
            $reg = CreditModel::where('user_id',$user->id)->where('action',CreditModel::KEY_NEW_COMMENT)->where('source_id',$comment->id)->first();
            $this->credit($reg,CreditModel::KEY_NEW_COMMENT,$user->id,$comment,'回复成功');
            $reg1 = CreditModel::where('user_id',$source->user_id)->where('action',$action1)->where('source_id',$comment->id)->first();
            $this->credit($reg1,$action1,$source->user_id,$comment,$source_subject);

        }
        //阅读发文
        $action = CreditModel::KEY_READHUB_NEW_SUBMISSION;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        $submissions = Submission::where('user_id',$user->id)->get();
        foreach ($submissions as $submission) {
            $this->credit('',$action,$user->id,$submission,'动态分享');
        }
        //分享成功
        $action = CreditModel::KEY_SHARE_SUCCESS;
        $models = CreditModel::where('user_id',$user->id)->where('action',$action)->get();
        foreach ($models as $model) {
            $this->credit($model,$action,$user->id,'','');
        }
        //专业问答评价&专业问答围观者评价
        CreditModel::where('user_id',$user->id)->whereIn('action',['rate_answer','feedback_rate_answer','new_answer_feedback'])->delete();
        $feedbacks = Feedback::where('user_id',$user->id)->where('source_type',Answer::class)->get();
        foreach ($feedbacks as $feedback) {
            if ($feedback->star >= 4) {
                $action = CreditModel::KEY_RATE_ANSWER_GOOD;
            } else {
                $action = CreditModel::KEY_RATE_ANSWER_BAD;
            }
            $source = $feedback->source;
            $reg = CreditModel::where('user_id',$source->user_id)->where('action',$action)->where('source_id',$feedback->id)->first();
            $this->credit($reg,$action,$source->user_id,$feedback,'回答评价');
            $this->credit('',CreditModel::KEY_NEW_ANSWER_FEEDBACK,$user->id,$feedback,'回答评价');
        }
        //点赞
        $action = CreditModel::KEY_NEW_UPVOTE;
        $supports = Support::where("user_id",'=',$user->id)->get();
        foreach ($supports as $support) {
            $source = $support->source;
            if (!$source) continue;
            switch ($support->supportable_type) {
                case 'App\Models\Answer':
                    $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$support->supportable_id)->first();
                    $this->credit($reg,$action,$user->id,$source,'点赞回答');
                    $question = $source->question;
                    if ($question->question_type == 1) {
                        $action1 = CreditModel::KEY_ANSWER_UPVOTE;
                        $reg1 = CreditModel::where('user_id',$user->id)->where('action',$action1)->where('source_id',$support->supportable_id)->first();
                        $this->credit($reg1,$action1,$source->user_id,$source,'专业回答被点赞');
                    } else {
                        $action1 = CreditModel::KEY_COMMUNITY_ANSWER_UPVOTE;
                        $reg1 = CreditModel::where('user_id',$user->id)->where('action',$action1)->where('source_id',$support->supportable_id)->first();
                        $this->credit($reg1,$action1,$source->user_id,$source,'互动回答被点赞');
                    }
                    break;
                case 'App\Models\Submission':
                    $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$support->supportable_id)->first();
                    $this->credit($reg,$action,$user->id,$source,'点赞动态分享');
                    $action1 = CreditModel::KEY_READHUB_SUBMISSION_UPVOTE;
                    $reg1 = CreditModel::where('user_id',$source->user_id)->where('action',$action1)->where('source_id',$support->supportable_id)->first();
                    $this->credit($reg1,$action1,$source->user_id,$source,'动态分享被点赞');
                    break;
            }
            RateLimiter::instance()->increase('upvote:'.get_class($source),$source->id.'_'.$user->id,0);

        }
        //收藏
        $action = CreditModel::KEY_NEW_COLLECT;
        $collections = Collection::where('user_id',$user->id)->where('status',1)->get();
        foreach ($collections as $collect) {
            $source = $collect->source;
            $reg = CreditModel::where('user_id',$user->id)->where('action',$action)->where('source_id',$collect->source_id)->first();
            $this->credit($reg,$action,$user->id,$source,'收藏成功');
            switch ($collect->source_type) {
                case 'App\Models\Article':
                    $action1 = CreditModel::KEY_PRO_OPPORTUNITY_SIGNED;
                    $reg1 = CreditModel::where('user_id',$source->user_id)->where('action',$action1)->where('source_id',$collect->source_id)->first();
                    $this->credit($reg1,$action1,$source->user_id,$source,'项目机遇被报名');
                    break;
                case 'App\Models\Submission':
                    $action1 = CreditModel::KEY_READHUB_SUBMISSION_COLLECT;
                    $reg1 = CreditModel::where('user_id',$source->user_id)->where('action',$action1)->where('source_id',$collect->source_id)->first();
                    $this->credit($reg1,$action1,$source->user_id,$source,'动态分享被收藏');
                    break;
                case 'App\Models\Answer':
                    $action1 = CreditModel::KEY_COMMUNITY_ANSWER_COLLECT;
                    $reg1 = CreditModel::where('user_id',$source->user_id)->where('action',$action1)->where('source_id',$collect->source_id)->first();
                    $this->credit($reg1,$action1,$source->user_id,$source,'回答被收藏');
                    break;
            }
            RateLimiter::instance()->increase('collect:'.get_class($source),$collect->source_id.'_'.$collect->user_id,0);
        }
        //转发
        $action = CreditModel::KEY_SHARE_SUCCESS;
        $shares = CreditModel::where('user_id',$user->id)->where('action',$action)->get();
        foreach ($shares as $share) {
            $this->credit($share,$action,$share->user_id,$share,$share->source_subject);
        }
        $doings = Doing::where('user_id',$user->id)->whereIn('action',[Doing::ACTION_SHARE_SUBMISSION_SUCCESS,Doing::ACTION_SHARE_ANSWER_SUCCESS])->get();
        foreach ($doings as $doing) {
            switch ($doing->action) {
                case Doing::ACTION_SHARE_SUBMISSION_SUCCESS:
                    $action1 = CreditModel::KEY_READHUB_SUBMISSION_SHARE;
                    $source = Submission::where('slug',$doing->source_id)->first();
                    $source_subject = '动态分享被转发';
                    break;
                case Doing::ACTION_SHARE_ANSWER_SUCCESS:
                    $source = Answer::find($doing->source_id);
                    if ($source) {
                        $question = $source->question;
                        if ($question->question_type == 1) {
                            $action1 = CreditModel::KEY_ANSWER_SHARE;
                            $source_subject = '专业回答被转发';
                        } else {
                            $action1 = CreditModel::KEY_COMMUNITY_ANSWER_SHARE;
                            $source_subject = '互动回答被转发';
                        }
                    }
                    break;
            }
            if (isset($source) && $source) {
                $reg = CreditModel::where('user_id',$user->id)->where('action',$action1)->where('source_id',$source->id)->first();
                $this->credit($reg,$action1,$source->user_id,$source,$source_subject);
            }
        }

        //付费围观
        $action = CreditModel::KEY_PAY_FOR_VIEW_ANSWER;
        $orders = Order::where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->get();
        foreach ($orders as $order) {
            $answer = $order->answer()->first();
            if ($answer) {
                $reg = CreditModel::where('user_id',$answer->question->user_id)->where('action',$action)->where('source_id',$order->id)->first();
                $this->credit($reg,$action,$answer->question->user_id,$order,'问题被付费围观');
            }
        }
        //关注
        $attentions = Attention::where('user_id',$user->id)->get();
        $action = CreditModel::KEY_NEW_FOLLOW;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        foreach ($attentions as $attention) {
            $source = $attention->source;
            $this->credit('',$action,$user->id,$source,$attention->source_type,$attention->created_at);
            switch ($attention->source_type) {
                case 'App\Models\Question':
                    $action1 = CreditModel::KEY_COMMUNITY_ASK_FOLLOWED;
                    $reg = CreditModel::where('user_id',$source->user_id)->where('action',$action1)->where('source_id',$source->id)->first();
                    $this->credit($reg,$action1,$source->user_id,$source,get_class($source),$attention->created_at);
                    break;
                case 'App\Models\User':
                    break;
            }
        }
        //邀请回答
        $questionInvitions = QuestionInvitation::where('from_user_id',$user->id)->get();
        $action = CreditModel::KEY_COMMUNITY_ANSWER_INVITED;
        CreditModel::where('user_id',$user->id)->where('action',$action)->delete();
        foreach ($questionInvitions as $questionInvition) {
            $question = Question::find($questionInvition->question_id);
            $this->credit('',$action,$user->id,$question,$questionInvition->user_id,$questionInvition->created_at);
        }

        $total_coins = CreditModel::where('user_id',$user->id)->sum('coins');
        $total_credits = CreditModel::where('user_id',$user->id)->sum('credits');
        $userData = UserData::find($user->id);
        $userData->coins = $total_coins;
        $userData->credits = $total_credits;
        $userData->save();
    }

    public function credit($creditExist,$action,$user_id,$source,$source_subject,$created_at=''){
        try{
            $coins = Setting()->get('coins_'.$action);
            $credits = Setting()->get('credits_'.$action);
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
                    'created_at' => $created_at?:(string)$source->created_at
                ]);
            }
            return true;
        }catch (\Exception $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }
}
