<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 下午3:12
 * @email: wanghui@yonglibao.com
 */

//app首页
Route::post('home','IndexController@home')->middleware('jwt.auth');

//登陆注册认证类
Route::group(['prefix' => 'auth','namespace'=>'Account'], function() {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('refresh', ['uses'=>'AuthController@refreshToken']);

    Route::post('forgot', 'AuthController@forgetPassword');
    Route::post('sendPhoneCode', 'AuthController@sendPhoneCode');

    Route::post('logout', 'AuthController@logout')->middleware('jwt.auth');

    //微信公众号注册验证
    Route::post('wxgzh/check_rg', 'AuthController@checkWeiXinGzh');
    //微信公众号注册
    Route::post('wxgzh/register', 'AuthController@registerWeiXinGzh');

});

Route::group(['namespace'=>'Account'], function() {
    //用户个人名片
    Route::post('profile/resumeInfo','ProfileController@resumeInfo');
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

    //上传简历
    Route::post('profile/uploadResume','ProfileController@uploadResume');


    //用户oauth
    Route::post('oauth/{type}/callback',['uses'=>'OauthController@callback'])->where(['type'=>'(weixinapp|weixin_gzh)']);

    //资金明细
    Route::post('account/money_log','ProfileController@moneyLog');
    //个人钱包
    Route::post('account/wallet','ProfileController@wallet');


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
    Route::post('account/edu/list','EduController@showList');


    //工作经历
    Route::post('account/job/store','JobController@store');
    Route::post('account/job/update','JobController@update');
    Route::post('account/job/destroy','JobController@destroy');
    Route::post('account/job/list','JobController@showList');

    //培训经历
    Route::post('account/train/store','TrainController@store');
    Route::post('account/train/update','TrainController@update');
    Route::post('account/train/destroy','TrainController@destroy');
    Route::post('account/train/list','TrainController@showList');

    //项目经历
    Route::post('account/project/store','ProjectController@store');
    Route::post('account/project/update','ProjectController@update');
    Route::post('account/project/destroy','ProjectController@destroy');
    Route::post('account/project/list','ProjectController@showList');

    /*关注问题、人、标签*/
    Route::post('follow/{source_type}',['uses'=>'FollowController@store'])->where(['source_type'=>'(question|tag|user)']);
    /*我的关注*/
    Route::post('followed/{source_type}',['uses'=>'FollowController@attentions'])->where(['source_type'=>'(questions|tags|users)']);

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
    //IAP结果验证
    Route::post('pay/iap_notify','NotifyController@iapNotify');
});

//提现
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Withdraw'], function() {
    //提现请求
    Route::post('withdraw/request','WithdrawController@request');

});


//加载标签
Route::post('tags/load','TagsController@load')->middleware('jwt.auth');

//上传图片
Route::post('upload/img','ImageController@upload')->middleware('jwt.auth');

//意见反馈
Route::post('system/feedback','SystemController@feedback')->middleware('jwt.auth');

//功能预告信息收集
Route::post('system/func_zan','SystemController@funcZan')->middleware('jwt.auth');

//保存用户设备信息
Route::post('system/device','SystemController@device')->middleware('jwt.auth');

//检测app版本
Route::post('system/version','SystemController@appVersion');

//支付参数
Route::post('pay/config','SystemController@getPayConfig');


//获取服务条款
Route::post('system/service_register','SystemController@serviceRegister');
//获取关于我们
Route::post('system/service_about','SystemController@serviceAbout');
//获取常见问题
Route::post('system/service_help','SystemController@serviceHelp');
//获取提问帮助设置
Route::post('system/service_qa_help','SystemController@serviceQaHelp');
//获取应用市场地址
Route::post('system/app_market_url','SystemController@getAppMarketUrl')->middleware('jwt.auth');


//消息模块
Route::group(['middleware' => ['jwt.auth','ban.user']], function() {
    //通知列表
    Route::post('notification/list','NotificationController@list');
    //统计
    Route::post('notification/count','NotificationController@count');

});


//项目模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'project','namespace'=>'Project'], function() {
    //项目列表
    Route::post('myList','ProjectController@myList');
    //添加项目
    Route::post('submit','ProjectController@submit');
    //修改项目
    Route::post('update','ProjectController@update');
    //删除项目
    Route::post('destroy','ProjectController@destroy');

});

//微信小程序
Route::group(['namespace'=>'Weapp'], function() {
    //获取用户登陆信息
    Route::post('weapp/user/info','UserController@getWxUserInfo');
});

Route::group(['middleware' => ['jwt.auth','ban.user'], 'namespace'=>'Weapp'], function() {
    //提问
    Route::post('weapp/question/store','QuestionController@store');
    //添加提问图片
    Route::post('weapp/question/add_image','QuestionController@addImage');
    //提问列表
    Route::post('weapp/question/allList','QuestionController@allList');
    //问题回复列表
    Route::post('weapp/question/loadAnswer','QuestionController@loadAnswer');


    //问题详情
    Route::post('weapp/question/info','QuestionController@info');
    //我的提问列表
    Route::post('weapp/question/myList','QuestionController@myList');
    //回答
    Route::post('weapp/answer/store','AnswerController@store');

});