<?php namespace App\Api\Controllers\Share;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Http\Requests;
use App\Models\Answer;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

class WechatController extends Controller
{


    public function jssdk(Request $request)
    {
        $validateRules = [
            'current_url' => 'required'
        ];

        $this->validate($request,$validateRules);
        $current_url = $request->input('current_url');
        $wechat = app('wechat');
        $js = $wechat->js;
        $js->setUrl($current_url);
        $config = $js->config(['onMenuShareTimeline','onMenuShareQQ','onMenuShareAppMessage', 'onMenuShareWeibo'],false,false,false);
        return self::createJsonData(true,['config'=>$config]);
    }

    public function shareSuccess(Request $request){
        $validateRules = [
            'target' => 'required'
        ];

        $this->validate($request,$validateRules);
        $user = $request->user();
        if ($user) {
            $this->credit($user->id,Credit::KEY_SHARE_SUCCESS,0,$request->input('target'));
            $source_type = $request->input('target_type');
            $source_type_class = '';
            $refer_user_id = 0;
            $action = '';
            $target_id = $request->input('target_id');
            switch ($source_type){
                case 'answer':
                    $source_type_class = Answer::class;
                    $action = Doing::ACTION_SHARE_ANSWER_SUCCESS;
                    $answer = Answer::find($target_id);
                    if ($answer) {
                        $refer_user_id = $answer->user_id;
                        $question = $answer->question;
                        if ($question->question_type == 1) {
                            $this->credit($refer_user_id,Credit::KEY_ANSWER_SHARE,$target_id,'专业回答被转发');
                        } else {
                            $this->credit($refer_user_id,Credit::KEY_COMMUNITY_ANSWER_SHARE,$target_id,'互动回答被转发');
                        }
                    }
                    break;
                case 'question':
                    $source_type_class = Question::class;
                    $action = Doing::ACTION_SHARE_QUESTION_SUCCESS;
                    $question = Question::find($target_id);
                    if ($question) {
                        if ($question->question_type == 1) {
                            //专业问题
                            $action = Doing::ACTION_SHARE_ANSWER_SUCCESS;
                            $source_type_class = Answer::class;
                            $bestAnswer = $question->answers()->where('adopted_at','>',0)->orderBy('id','desc')->get()->last();
                            $refer_user_id = $bestAnswer->user_id;
                            $target_id = $bestAnswer->id;
                        } else {
                            $refer_user_id = $question->user_id;
                        }
                    }
                    break;
                case 'invite_answer':
                    $source_type_class = Question::class;
                    $action = Doing::ACTION_SHARE_INVITE_ANSWER_SUCCESS;
                    $question = Question::find($target_id);
                    if ($question) {
                        $refer_user_id = $question->user_id;
                    }
                    break;
                case 'resume':
                    $source_type_class = User::class;
                    $action = Doing::ACTION_SHARE_RESUME_SUCCESS;
                    $user = User::select('id')->where('uuid',$target_id)->first();
                    if ($user) {
                        $refer_user_id = $user->id;
                        $target_id = $user->id;
                    }
                    break;
                case 'submission':
                    $source_type_class = Submission::class;
                    $action = Doing::ACTION_SHARE_SUBMISSION_SUCCESS;
                    $submission = Submission::where('slug',$target_id)->first();
                    if (!$submission) {
                        $submission = Submission::find($target_id);
                    }
                    if ($submission) {
                        $refer_user_id = $submission->user_id;
                        $target_id = $submission->id;
                        $submission->increment('share_number');
                        $this->calculationSubmissionRate($submission->id);
                        $this->credit($refer_user_id,Credit::KEY_READHUB_SUBMISSION_SHARE,$submission->id,'动态分享被转发');
                    }
                    break;
                case 'invite_register':
                    $source_type_class = User::class;
                    $action = Doing::ACTION_SHARE_INVITE_REGISTER_SUCCESS;
                    $invite_user = User::where('rc_code',$target_id)->first();
                    if ($invite_user) {
                        $refer_user_id = $invite_user->id;
                        $target_id = $invite_user->id;
                    }
                    break;
            }
            if ($source_type_class) {
                $this->doing($user,$action,$source_type_class,$target_id,$request->input('title'),$request->input('target'),0,$refer_user_id);
            }
        }
        return self::createJsonData(true);
    }

}
