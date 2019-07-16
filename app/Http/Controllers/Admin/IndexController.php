<?php

namespace App\Http\Controllers\Admin;

use App\Models\Activity\Coupon;
use App\Models\Answer;
use App\Models\Credit;
use App\Models\Doing;
use App\Models\Feedback;
use App\Models\LoginRecord;
use App\Models\Pay\Order;
use App\Models\Pay\Settlement;
use App\Models\Pay\UserMoney;
use App\Models\Pay\Withdraw;
use App\Models\Question;
use App\Models\Scraper\BidInfo;
use App\Models\Scraper\WechatWenzhangInfo;
use App\Models\Submission;
use App\Models\Tag;
use App\Models\Taggable;
use App\Models\Task;
use App\Models\User;
use App\Models\UserData;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class IndexController extends AdminController
{
    /**
     *显示后台首页
     */
    public function index()
    {
        $data = Cache::remember('admin_index_dashboard',60,function () {
            $totalUserNum = User::count();
            $totalUserNumHasPhone = User::whereNotNull('mobile')->count();
            $totalUserNumHasEmail = $totalUserNum - User::where('email','')->count();

            $totalQuestionNum = Question::count();
            $totalFeedbackNum = Feedback::count();
            $totalAnswerNum = Answer::count();
            $totalTasks = Task::count();
            $totalUndoTasks = Task::where('status',0)->count();
            $totalUndoTaskUsers = Task::select('user_id')->distinct()->where('status',0)->count('user_id');

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
            /*$questionConfirmeds = Doing::where('action','question_answer_confirmed')->get();
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
            $questionAvgAnswerTime = $questionAnswerCount ? round($questionAnswerSecond/60/$questionAnswerCount,2): 0;*/

            //未处理文章数
            $articlesTodo = WechatWenzhangInfo::where('topic_id',0)->where('status',1)->where('date_time','>=',date('Y-m-d 00:00:00',strtotime('-1 days')))->count();
            //未处理招标数
            $bidTodo = BidInfo::where('status',1)->count();
            //未处理招聘数
            $recruitTodo = 0;

            $userChart = $this->drawUserChart();
            $questionChart = $this->drawQuestionChart();
            $systemInfo = $this->getSystemInfo();
            $submissionTextCount = Submission::where('type','text')->count();
            $submissionLinkCount = Submission::where('type','link')->count();
            //邀请用户统计
            $rcUsers = User::selectRaw('count(*) as total,rc_uid')->groupBy('rc_uid')->orderBy('total','desc')->get();
            $coinUsers = UserData::orderBy('coins','desc')->take(50)->get();
            $creditUsers = UserData::orderBy('credits','desc')->take(50)->get();
            $signTotalCouponMoney = Coupon::whereIn('coupon_type',[Coupon::COUPON_TYPE_DAILY_SIGN_SMALL,Coupon::COUPON_TYPE_DAILY_SIGN_BIG])->sum('coupon_value');
            $newbieTotalCouponMoney = Coupon::where('coupon_type',Coupon::COUPON_TYPE_NEW_REGISTER_INVITATION)->sum('coupon_value');
            //用户等级统计
            $userLevels= UserData::selectRaw('count(*) as total,user_level')->groupBy('user_level')->orderBy('total','desc')->get();
            //用户余额
            $totalBalance = UserMoney::sum('total_money');
            //待结算金融
            $totalSettlement = UserMoney::sum('settlement_money');
            $userMoney= UserMoney::orderBy('total_money','desc')->take(50)->get();
            $userWithdrawMoneyList = Withdraw::select('user_id',DB::raw('SUM(amount) as total_amount'))
                ->where('status',Withdraw::WITHDRAW_STATUS_SUCCESS)
                ->orderBy('total_amount','desc')
                ->groupBy('user_id')
                ->take(50)->get();
            //提现金额
            $withDrawMoney = Withdraw::where('status',Withdraw::WITHDRAW_STATUS_SUCCESS)->sum('amount');
            //热门标签
            $taggables =  Taggable::select('tag_id',DB::raw('COUNT(id) as total_num'))->groupBy('tag_id')
                ->orderBy('total_num','desc')
                ->take(100)
                ->get();
            $hotTags = [];
            foreach ($taggables as $taggable) {
                $tagInfo = Tag::find($taggable->tag_id);
                $hotTags[] = [
                    'tag_id' => $tagInfo->id,
                    'tag_name'  => $tagInfo->name,
                    'total_num' => $taggable->total_num
                ];
            }
            //累计围观总数
            $totalPayForView = Order::where('status',Order::PAY_STATUS_SUCCESS)->where('return_param','view_answer')->count();
            //累计收入总数
            $totalFeeMoney = Settlement::where('status',Settlement::SETTLEMENT_STATUS_SUCCESS)->sum('actual_fee');
            //搜索统计
            $searchCount = RateLimiter::instance()->hGetAll('search-word-count');
            arsort($searchCount);
            $searchCount = array_slice($searchCount,0,100,true);

            //订阅数
            $subscribePushCount = User::where('site_notifications','like','%"push_daily_subscribe":1%')->orWhere('site_notifications','like','%"push_daily_subscribe": 1%')->count();
            $subscribeEmailCount = User::where('site_notifications','like','%email_daily_subscribe%@%')->count();
            $subscribeWechatCount = User::where('site_notifications','like','%"wechat_daily_subscribe":1%')->orWhere('site_notifications','like','%"wechat_daily_subscribe": 1%')->count();

            return compact('totalUserNum','totalQuestionNum','totalFeedbackNum',
                    'totalAnswerNum',
                    'totalUserNumHasPhone',
                    'totalUserNumHasEmail',
                    //'userInfoCompleteTime',
                    'userInfoCompletePercent',
                    //'questionAvaConfirmTime',
                    //'questionAvgAnswerTime',
                    'submissionLinkCount',
                    'submissionTextCount',
                    'subscribePushCount',
                    'subscribeEmailCount',
                    'subscribeWechatCount',
                    'totalTasks',
                    'totalUndoTasks',
                    'totalUndoTaskUsers',
                    'rcUsers',
                    'coinUsers',
                    'creditUsers',
                    'signTotalCouponMoney',
                    'newbieTotalCouponMoney',
                    'userLevels',
                    'totalBalance',
                    'totalSettlement',
                    'userMoney',
                    'withDrawMoney',
                    'userWithdrawMoneyList',
                    'hotTags',
                    'totalPayForView',
                    'totalFeeMoney',
                    'searchCount',
                    'articlesTodo',
                    'bidTodo',
                    'recruitTodo',
                    'userChart','questionChart','systemInfo')
            ;
        });


        return view("admin.index.index")->with($data);
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

        for( $i=0 ; $i < 30 ; $i++ ){
            $labelTimes[$i] = Carbon::createFromTimestamp( Carbon::today()->timestamp - (29-$i) * 24 * 3600 );
            $chartLabels[$i] = '"'.$labelTimes[$i]->month.'月-'.$labelTimes[$i]->day.'日'.'"';
        }

        $nowTime = Carbon::now();

        $users = User::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();

        $registerRange = $verifyRange = $authRange = $signRange = $loginRange = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

        for( $i=0 ; $i < 30 ; $i++ ){
            $startTime = $labelTimes[$i];
            $endTime = $nowTime;
            if(isset($labelTimes[$i+1])){
                $endTime = $labelTimes[$i+1];
            }
            $signRange[$i] = Credit::where('action',Credit::KEY_FIRST_USER_SIGN_DAILY)
                ->where('created_at','>',$startTime)
                ->where('created_at','<',$endTime)
                ->count();
            $loginRange[$i] = LoginRecord::select('user_id')->where('created_at','>',$startTime)
                ->where('created_at','<',$endTime)
                ->distinct()
                ->count('user_id');

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

        return ['labels'=>$chartLabels,'registerUsers'=>$registerRange,'recommendUsers'=>$verifyRange,'authUsers'=>$authRange, 'signUsers'=>$signRange,'loginUsers'=>$loginRange];
    }

    private function drawQuestionChart()
    {

        /*生成Labels*/
        $labelTimes = $chartLabels = [];
        for( $i=0 ; $i < 30 ; $i++ ){
            $labelTimes[$i] = Carbon::createFromTimestamp( Carbon::today()->timestamp - (29-$i) * 24 * 3600 );
            $chartLabels[$i] = '"'.$labelTimes[$i]->month.'月-'.$labelTimes[$i]->day.'日'.'"';
        }

        $nowTime = Carbon::now();

        $questions = Question::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $answers = Answer::where('status',Answer::ANSWER_STATUS_FINISH)->where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $feedbacks = Feedback::where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $submissions = Submission::where('status',1)->where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();
        $shares = Credit::where('action','share_success')->where('created_at','>',$labelTimes[0])->where('created_at','<',$nowTime)->get();

        $questionRange = $answerRange = $feedbackRange = $submissionTextRange = $submissionLinkRange = $shareRange = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];

        for( $i=0 ; $i < 30 ; $i++ ){
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
            //分享统计
            foreach ($shares as $share) {
                if( $share->created_at > $startTime && $share->created_at < $endTime ){
                    $shareRange[$i]++;
                }
            }

        }

        return [
            'labels'  => $chartLabels,
            'questionRange' => $questionRange,
            'answerRange' => $answerRange,
            'feedbackRange' => $feedbackRange,
            'submissionLinkRange' => $submissionLinkRange,
            'submissionTextRange' => $submissionTextRange,
            'shareRange' => $shareRange
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
