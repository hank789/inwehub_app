<?php
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:46
 * @email: wanghui@yonglibao.com
 */

//首页
Route::group(['namespace'=>'Web'], function() {

    Route::get('/',['as'=>'website.index','uses'=>'IndexController@index']);

    Route::get('/service/register',['as'=>'website.service.register','uses'=>'ServiceController@register']);
    Route::get('/service/about',['as'=>'website.service.about','uses'=>'ServiceController@about']);

});

//支付异步通知
Route::group(['namespace'=>'Pay'], function() {
    Route::get('pay/notify/:type',['as'=>'website.pay.notify','uses'=>'NotifyController@payNotify']);
});
