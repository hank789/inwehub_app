<?php

namespace App\Providers;
use App\Models\Activity\Coupon;
use App\Models\Authentication;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Company\Company;
use App\Models\DownVote;
use App\Models\Groups\GroupMember;
use App\Models\Pay\Withdraw;
use App\Models\Question;
use App\Models\Submission;
use App\Models\Support;
use App\Models\User;
use App\Models\UserInfo\EduInfo;
use App\Models\UserInfo\JobInfo;
use App\Models\UserInfo\ProjectInfo;
use App\Models\UserInfo\TrainInfo;
use App\Observers\AuthenticationObserver;
use App\Observers\CollectObserver;
use App\Observers\CommentObserver;
use App\Observers\CompanyObserver;
use App\Observers\CouponObserver;
use App\Observers\DownvoteObserver;
use App\Observers\MemberGroupObserver;
use App\Observers\QuestionObserver;
use App\Observers\SubmissionObserver;
use App\Observers\SupportObserver;
use App\Observers\UserEduObserver;
use App\Observers\UserJobObserver;
use App\Observers\UserObserver;
use App\Observers\UserProjectObserver;
use App\Observers\UserTrainObserver;
use App\Observers\WithdrawObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //设置时间
        Carbon::setLocale(Config::get('app.locale'));

        // 添加验证手机号码规则
        Validator::extend('cn_phone', function ($attribute, $value,$parameters, $validator) {
            return preg_match('/^(\+?0?86\-?)?((13\d|14[57]|15[^4,\D]|17[35678]|18\d)\d{8}|170[059]\d{7})$/', $value);
        });

        /*Queue::failing(function ($job) {
            // Notify team of failing job...
            Log::error('队列任务执行出错',['connection'=>$job->connectionName,'job'=>$job->job,'msg'=>$job->exception->getMessage()]);
        });*/
        Log::listen(function($log)
        {
            if( get_class($log) === 'Illuminate\Log\Events\MessageLogged' && $log->level === 'error' && !($log->message instanceof \Exception)){
                try{
                    switch($log->level){
                        case 'error':
                            //Notify team of error
                            \Slack::to(config('slack.exception_channel'))->attach([
                                'pretext' => '错误详细信息',
                                'color' => 'danger',
                                'fields' => [
                                    [
                                        'title' => 'Stack trace',
                                        'value' => is_array($log->context) ? json_encode($log->context,JSON_UNESCAPED_UNICODE):$log->context
                                    ]
                                ]
                            ])->send($log->message);
                            break;
                    }
                }catch (\Exception $e){
                    app('sentry')->captureException($e);
                }
            }
        });

        //事件监听
        Question::observe(QuestionObserver::class);
        Authentication::observe(AuthenticationObserver::class);
        User::observe(UserObserver::class);
        JobInfo::observe(UserJobObserver::class);
        EduInfo::observe(UserEduObserver::class);
        ProjectInfo::observe(UserProjectObserver::class);
        TrainInfo::observe(UserTrainObserver::class);
        Company::observe(CompanyObserver::class);
        Withdraw::observe(WithdrawObserver::class);
        Collection::observe(CollectObserver::class);
        Comment::observe(CommentObserver::class);
        Support::observe(SupportObserver::class);
        Submission::observe(SubmissionObserver::class);
        Coupon::observe(CouponObserver::class);
        GroupMember::observe(MemberGroupObserver::class);
        DownVote::observe(DownvoteObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
