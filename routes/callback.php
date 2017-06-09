<?php
/**
 * @author: wanghui
 * @date: 2017/6/9 下午6:44
 * @email: wanghui@yonglibao.com
 */

//支付异步通知
Route::group(['namespace'=>'Pay'], function() {
    Route::get('pay/notify/{type}',['as'=>'website.pay.notify','uses'=>'NotifyController@payNotify']);
    Route::post('pay/notify/{type}',['as'=>'website.pay.notify','uses'=>'NotifyController@payNotify']);
});