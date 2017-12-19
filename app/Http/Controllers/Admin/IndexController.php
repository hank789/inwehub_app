<?php

namespace App\Http\Controllers\Admin;

use App\Models\Answer;
use App\Models\Article;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use App\Models\UserRegistrationCode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class IndexController extends AdminController
{
    /**
     *显示后台首页
     */
    public function index()
    {
        $totalUserNum = User::count();
        $totalQuestionNum = Question::count();
        $totalFeedbackNum = Feedback::count();
        $totalAnswerNum = Answer::count();
        $totalTasks = Task::count();
        $totalUndoTasks = Task::where('status',0)->count();
        $totalUndoTaskUsers = Task::where('status',0)->groupBy('user_id')->count();

        //邀请码总数
        //$totalUrcNum = UserRegistrationCode::count();
        //邀请码激活数
        //$totalActiveUrcNum = UserRegistrationCode::where('status',UserRegistrationCode::CODE_STATUS_USED)->count();
        //邀请码失效数
        //$totalInActiveUrcNum = UserRegistrationCode::where('status',UserRegistrationCode::CODE_STATUS_EXPIRED)->count();

        //简历平均完成时间
        $userInfoCompletes = Credit::where('action','user_info_complete')->get();
        $userInfoCompleteCount = $userInfoCompletes->count();
        $userInfoCompleteTime = 0;
        $diffSecond = 0;
        foreach($userInfoCompletes as $userInfoComplete){
            $registerTime = Credit::where('user_id',$userInfoComplete->user_id)->where('action','register')->first();
            if($registerTime){
                $diffSecond += strtotime($userInfoComplete->created_at) - strtotime($registerTime->created_at);
            }
        }
        if($userInfoCompleteCount){
            $userInfoCompleteTime = round($diffSecond/$userInfoCompleteCount/60,2);
        }
        //简历完成率
        $userInfoCompletePercent = 0;
        if($userInfoCompleteCount){
            $userInfoCompletePercent = 100 * round($userInfoCompleteCount/$totalUserNum,2);
        }

        //问题平均接单时间
        $questionConfirmeds = Doing::where('action','question_answer_confirmed')->get();
        $questionCount = $questionConfirmeds->count();
        $questionAnswerCount = Doing::where('action','question_answered')->count();
        $questionConfirmSecond = 0;
        $questionAnswerSecond = 0;

        foreach($questionConfirmeds as $questionConfirmed){
            $questionSubmit = Doing::where('action','question_submit')->where('source_id',$questionConfirmed->source_id)->where('source_type','App\Models\Question')->first();
            $questionConfirmSecond += strtotime($questionConfirmed->created_at) - strtotime($questionSubmit->created_at);

            $questionAnswer = Doing::where('action','question_answered')->where('source_id',$questionConfirmed->source_id)->where('source_type','App\Models\Question')->first();
            if($questionAnswer){
                $questionAnswerSecond += strtotime($questionAnswer->created_at) - strtotime($questionConfirmed->created_at);
            }

        }
        $questionAvaConfirmTime = $questionCount ? round($questionConfirmSecond/60/$questionCount,2) : 0;
        $questionAvgAnswerTime = $questionAnswerCount ? round($questionAnswerSecond/60/$questionAnswerCount,2): 0;

        $userChart = $this->drawUserChart();
        $questionChart = $this->drawQuestionChart();
        $systemInfo = $this->getSystemInfo();
        $submissionTextCount = Submission::where('type','text')->count();
        $submissionLinkCount = Submission::where('type','link')->count();

        return view("admin.index.index")->with(compact('totalUserNum','totalQuestionNum','totalFeedbackNum',
            'totalAnswerNum',
            'userInfoCompleteTime',
            'userInfoCompletePercent',
            'questionAvaConfirmTime',
            'questionAvgAnswerTime',
            'submissionLinkCount',
            'submissionTextCount',
            'totalTasks',
            'totalUndoTasks',
            'totalUndoTaskUsers',
            'userChart','questionChart','systemInfo'));
    }


    /*显示或隐藏sidebar*/
    public function sidebar(Request $request){
        Session::forget('sidebar_collapse');
        Session::put("sidebar_collapse",$request->get('collapse'));
        return response()->json(Session::get('sidebar_collapse'));
    }


    private function drawUserChart()
    {

        /*生成Labels*/
        $labelTimes = $chartLabels = [];

        for( $i=0 ; $i < 7 ; $i++ ){
            $labelTimes[$i] = Carbon::createFromTimestamp( Carbon::today()->timestamp - (6-$i) * 24 * 3600 );
            $chartLabels[$i] = '"'.$labelTimes[$i]->month.'月-'.$labelTimes[$i]->day.'日'.'"';
        }

        $nowTime = Carbon::now();

        $users = User::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();

        $registerRange = $verifyRange = $authRange = [0,0,0,0,0,0,0];

        for( $i=0 ; $i < 7 ; $i++ ){
            $startTime = $labelTimes[$i];
            $endTime = $nowTime;
            if(isset($labelTimes[$i+1])){
                $endTime = $labelTimes[$i+1];
            }
            foreach($users as $user){
                if( $user->created_at > $startTime && $user->created_at < $endTime ){
                    $registerRange[$i]++;
                    if( $user->rc_uid > 0 ){
                        $verifyRange[$i]++;
                    }

                    if($user->userData && $user->userData->authentication_status === 1){
                        $authRange[$i]++;
                    }
                }
            }

        }

        return ['labels'=>$chartLabels,'registerUsers'=>$registerRange,'recommendUsers'=>$verifyRange,'authUsers'=>$authRange];
    }

    private function drawQuestionChart()
    {

        /*生成Labels*/
        $labelTimes = $chartLabels = [];
        for( $i=0 ; $i < 7 ; $i++ ){
            $labelTimes[$i] = Carbon::createFromTimestamp( Carbon::today()->timestamp - (6-$i) * 24 * 3600 );
            $chartLabels[$i] = '"'.$labelTimes[$i]->month.'月-'.$labelTimes[$i]->day.'日'.'"';
        }

        $nowTime = Carbon::now();


        $questions = Question::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $answers = Answer::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $feedbacks = Feedback::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $submissions = Submission::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();


        $questionRange = $answerRange = $feedbackRange = $submissionTextRange = $submissionLinkRange = [0,0,0,0,0,0,0];

        for( $i=0 ; $i < 7 ; $i++ ){
            $startTime = $labelTimes[$i];
            $endTime = $nowTime;
            if(isset($labelTimes[$i+1])){
                $endTime = $labelTimes[$i+1];
            }

            /*问题统计*/
            foreach($questions as $question){
                if( $question->created_at > $startTime && $question->created_at < $endTime ){
                    $questionRange[$i]++;
                }
            }

            /*回答统计*/
            foreach($answers as $answer){
                if( $answer->created_at > $startTime && $answer->created_at < $endTime ){
                    $answerRange[$i]++;
                }
            }
            /*评价统计*/
            foreach($feedbacks as $feedback){
                if( $feedback->created_at > $startTime && $feedback->created_at < $endTime ){
                    $feedbackRange[$i]++;
                }
            }

            //动态统计
            foreach($submissions as $submission){
                if($submission->type == 'text' && $submission->created_at > $startTime && $submission->created_at < $endTime ){
                    $submissionTextRange[$i]++;
                }
                if($submission->type == 'link' && $submission->created_at > $startTime && $submission->created_at < $endTime ){
                    $submissionLinkRange[$i]++;
                }
            }

        }

        return [
            'labels'  => $chartLabels,
            'questionRange' => $questionRange,
            'answerRange' => $answerRange,
            'feedbackRange' => $feedbackRange,
            'submissionLinkRange' => $submissionLinkRange,
            'submissionTextRange' => $submissionTextRange
        ];

    }


    private function getSystemInfo()
    {
        $systemInfo['phpVersion'] = PHP_VERSION;
        $systemInfo['runOS'] = PHP_OS;
        $systemInfo['maxUploadSize'] = ini_get('upload_max_filesize');
        $systemInfo['maxExecutionTime'] = ini_get('max_execution_time');
        $systemInfo['hostName'] = '';
        if(isset($_SERVER['SERVER_NAME'])){
            $systemInfo['hostName'] .= $_SERVER['SERVER_NAME'].' / ';
        }
        if(isset($_SERVER['SERVER_ADDR'])){
            $systemInfo['hostName'] .= $_SERVER['SERVER_ADDR'].' / ';
        }
        if(isset($_SERVER['SERVER_PORT'])){
            $systemInfo['hostName'] .= $_SERVER['SERVER_PORT'];
        }
        $systemInfo['serverInfo'] = '';
        if(isset($_SERVER['SERVER_SOFTWARE'])){
            $systemInfo['serverInfo'] = $_SERVER['SERVER_SOFTWARE'];
        }
        return $systemInfo;
    }


}
