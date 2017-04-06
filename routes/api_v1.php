<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 下午3:12
 * @email: wanghui@yonglibao.com
 */
Route::group(['prefix' => 'auth','namespace'=>'Auth'], function() {
    Route::post('register', 'UserController@register');
    Route::post('login', 'UserController@login');


    Route::post('recovery', 'ForgotPasswordController@sendResetEmail');
    Route::post('reset', 'ResetPasswordController@resetPassword');
});

Route::group(['middleware' => 'jwt.auth'], function() {
    Route::get('protected', function() {
        return response()->json([
            'message' => 'Access to this item is only for authenticated user. Provide a token in your request!'
        ]);
    });

    Route::get('refresh', [
        'middleware' => 'jwt.refresh',
        function() {
            return response()->json([
                'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
            ]);
        }
    ]);
});

Route::get('hello', function() {
    return response()->json([
        'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
    ]);
});
