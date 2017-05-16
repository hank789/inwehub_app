<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 下午3:12
 * @email: wanghui@yonglibao.com
 */

//app首页
Route::post('home','IndexController@home');


//登陆注册认证类
Route::group(['prefix' => 'auth','namespace'=>'Account'], function() {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('refresh', ['uses'=>'AuthController@refreshToken']);


    Route::post('forgot', 'AuthController@forgetPassword');
    Route::post('sendPhoneCode', 'AuthController@sendPhoneCode');

    Route::post('logout', 'AuthController@logout')->middleware('jwt.auth');

});


//用户信息
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Account'], function() {
    //用户信息
    Route::post('profile/info','ProfileController@info');
    //修改用户头像
    Route::post('profile/updateAvatar','ProfileController@postAvatar');
    //用户修改密码
    Route::post('profile/password','ProfileController@updatePassword');
    //用户修改基本信息
    Route::post('profile/update','ProfileController@update');

    //资金明细
    Route::post('account/money_log','ProfileController@moneyLog');


    //专家认证申请
    Route::post('expert/apply','ExpertController@apply');
    //推荐专家
    Route::post('expert/recommend','ExpertController@recommend');
    //专家审核情况
    Route::post('expert/info','ExpertController@info');


    //教育经历
    Route::post('account/edu/store','EduController@store');
    Route::post('account/edu/update','EduController@update');
    Route::post('account/edu/destroy','EduController@destroy');

    //工作经历
    Route::post('account/job/store','JobController@store');
    Route::post('account/job/update','JobController@update');
    Route::post('account/job/destroy','JobController@destroy');
    //培训经历
    Route::post('account/train/store','TrainController@store');
    Route::post('account/train/update','TrainController@update');
    Route::post('account/train/destroy','TrainController@destroy');
    //项目经历
    Route::post('account/project/store','ProjectController@store');
    Route::post('account/project/update','ProjectController@update');
    Route::post('account/project/destroy','ProjectController@destroy');

});


//问答模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Ask'], function() {
    //回答反馈
    Route::post('answer/feedback','AnswerController@feedback');
    //我的回答列表
    Route::post('answer/myList','AnswerController@myList');
    //我的提问列表
    Route::post('question/myList','QuestionController@myList');
    //拒绝回答
    Route::post('question/rejectAnswer','QuestionController@rejectAnswer');
    //提问请求
    Route::post('question/request','QuestionController@request');
    //新建回答
    Route::post('answer/store','AnswerController@store');
    //新建提问
    Route::post('question/store','QuestionController@store');
    //查看点评
    Route::post('answer/feedbackInfo','AnswerController@feedbackInfo');
    //问题详情
    Route::post('question/info','QuestionController@info');

});

//任务模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Task'], function() {
    //我的任务列表
    Route::post('task/myList','TaskController@myList');

});

//支付
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Pay'], function() {
    //支付请求
    Route::post('pay/request','PayController@request');

});

//提现
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Withdraw'], function() {
    //提现请求
    Route::post('withdraw/request','WithdrawController@request');

});


//加载标签
Route::post('tags/load','TagsController@load')->middleware('jwt.auth');

//意见反馈
Route::post('system/feedback','SystemController@feedback')->middleware('jwt.auth');

//保存用户设备信息
Route::post('system/device','SystemController@device')->middleware('jwt.auth');


//消息模块
Route::group(['middleware' => ['jwt.auth','ban.user']], function() {
    //通知列表
    Route::post('notification/list','NotificationController@list');
});