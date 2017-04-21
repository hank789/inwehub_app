<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 下午3:12
 * @email: wanghui@yonglibao.com
 */

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
    //专家认证申请
    Route::post('expert/apply','ExpertController@apply');

    Route::post('protected', function() {
        return response()->json([
            'message' => 'Access to this item is only for authenticated user. Provide a token in your request!'
        ]);
    });

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

//上传图片
Route::post('upload/uploadImg','UploadController@uploadImg')->middleware('jwt.auth');

//加载标签
Route::post('tags/load','TagsController@load')->middleware('jwt.auth');

