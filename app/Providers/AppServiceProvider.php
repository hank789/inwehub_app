<?php

namespace App\Providers;
use App\Models\Answer;
use App\Models\Authentication;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\User;
use App\Models\UserInfo\EduInfo;
use App\Models\UserInfo\JobInfo;
use App\Models\UserInfo\ProjectInfo;
use App\Models\UserInfo\TrainInfo;
use App\Observers\AnswerObserver;
use App\Observers\AuthenticationObserver;
use App\Observers\QuestionInvitationObserver;
use App\Observers\QuestionObserver;
use App\Observers\UserEduObserver;
use App\Observers\UserJobObserver;
use App\Observers\UserObserver;
use App\Observers\UserProjectObserver;
use App\Observers\UserTrainObserver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

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
            return preg_match('/^(\+?0?86\-?)?((13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7})$/', $value);
        });

        //事件监听
        Question::observe(QuestionObserver::class);
        Answer::observe(AnswerObserver::class);
        Authentication::observe(AuthenticationObserver::class);
        User::observe(UserObserver::class);
        JobInfo::observe(UserJobObserver::class);
        EduInfo::observe(UserEduObserver::class);
        ProjectInfo::observe(UserProjectObserver::class);
        TrainInfo::observe(UserTrainObserver::class);
        QuestionInvitation::observe(QuestionInvitationObserver::class);
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
