<?php
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:46
 * @email: wanghui@yonglibao.com
 */

//首页
Route::group(['namespace'=>'Web'], function() {

    Route::get('/',['as'=>'website.index','uses'=>'IndexController@index']);
    Route::get('/testPay',['as'=>'website.testPay','uses'=>'IndexController@testPay']);
    Route::post('/testNotify',['as'=>'website.testNotify','uses'=>'IndexController@testNotify']);

    Route::get('/articleInfo/{id}',['as'=>'website.articleInfo','uses'=>'IndexController@articleInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/trackEmail/{type}/{id}/{uid}',['as'=>'website.trackEmail','uses'=>'IndexController@trackEmail'])->where(['id'=>'[0-9]+']);
    Route::get('/unsubscribeEmail/{uid}',['as'=>'website.unsubscribeEmail','uses'=>'IndexController@unsubscribeEmail'])->where(['uid'=>'[0-9]+']);


    Route::get('/service/register',['as'=>'website.service.register','uses'=>'ServiceController@register']);
    Route::get('/service/about',['as'=>'website.service.about','uses'=>'ServiceController@about']);
    Route::get('/service/getQuestionShareImage/{qid}/{uid}',['uses'=>'ServiceController@getQuestionShareImage'])->where(['qid'=>'[0-9]+','uid'=>'[0-9]+']);

    Route::get('/weapp/getDemandShareLongInfo/{id}',['uses'=>'WeappController@getDemandShareLongInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getDemandShareShortInfo/{id}',['uses'=>'WeappController@getDemandShareShortInfo'])->where(['id'=>'[0-9]+']);

    Route::get('/weapp/getProductShareLongInfo/{id}',['uses'=>'WeappController@getProductShareLongInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getProductShareShortInfo/{id}',['uses'=>'WeappController@getProductShareShortInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getReviewShareLongInfo/{id}',['uses'=>'WeappController@getReviewShareLongInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getReviewShareShortInfo/{id}',['uses'=>'WeappController@getReviewShareShortInfo'])->where(['id'=>'[0-9]+']);
    Route::get('/weapp/getAlbumShareLongInfo/{id}',['uses'=>'WeappController@getAlbumShareLongInfo'])->where(['id'=>'[0-9]+']);

});


//微信公众号
Route::group(['namespace'=>'Wechat'], function() {
    Route::any('/wechat', 'WechatController@serve');
    Route::get('/wechat/oauth', 'WechatController@oauth');
    Route::any('/wechat/oauthCallback', 'WechatController@oauthCallback');
});