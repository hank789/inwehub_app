<?php
/**
 * @author: wanghui
 * @date: 2017/4/19 下午7:46
 * @email: wanghui@yonglibao.com
 */

//首页
Route::get('/',['as'=>'website.index','uses'=>'IndexController@index']);
