<?php
/**
 * @author: wanghui
 * @date: 2017/4/12 下午9:00
 * @email: wanghui@yonglibao.com
 */

Route::group(['prefix' => 'inwehub','namespace'=>'Inwehub','middleware'=>\Barryvdh\Cors\HandleCors::class], function() {

    Route::get('topic', 'TopicController@index');
    Route::get('topic/newCount', 'TopicController@newCount');

});