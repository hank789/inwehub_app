<?php
/**
 * @author: wanghui
 * @date: 2017/4/12 下午9:00
 * @email: wanghui@yonglibao.com
 */

Route::group(['prefix' => 'inwehub','namespace'=>'Inwehub'], function() {

    Route::get('topic', 'TopicController@index');
    Route::get('topic/newCount', 'TopicController@newCount');

    Route::get('news', 'NewsController@index');
    Route::get('news/newCount', 'NewsController@newCount');
});