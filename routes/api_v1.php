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



Route::group(['middleware' => 'jwt.auth','prefix' => 'account','namespace'=>'Account'], function() {
    //用户信息
    Route::post('show','ProfileController@show');

    Route::post('protected', function() {
        return response()->json([
            'message' => 'Access to this item is only for authenticated user. Provide a token in your request!'
        ]);
    });

});

Route::get('hello', function() {
    return response()->json([
        'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
    ]);
});

//上传图片
Route::post('upload/uploadImg','UploadController@uploadImg')->middleware('jwt.auth');