<?php
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:46
 * @email: wanghui@yonglibao.com
 */

//首页
Route::group(['namespace'=>'Web'], function() {

    Route::get('/',['as'=>'website.index','uses'=>'IndexController@index']);

});

//支付异步通知
Route::group(['namespace'=>'Pay'], function() {
    Route::get('pay/notify/:type',['as'=>'website.pay.notify','uses'=>'NotifyController@payNotify']);
});
