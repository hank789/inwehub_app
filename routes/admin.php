<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 上午11:46
 * @email: wanghui@yonglibao.com
 */
/*后台管理部分处理*/

/*用户登陆*/
Route::match(['get','post'],'login',['as'=>'admin.account.login','uses'=>'AccountController@login']);


/*用户退出*/
Route::get('logout',['as'=>'admin.account.logout','uses'=>'AccountController@logout']);

Route::get('system/index',['as'=>'admin.system.index','uses'=>'SystemController@index']);
Route::post('system/upgrade',['as'=>'admin.system.upgrade','uses'=>'SystemController@upgrade']);
Route::post('system/adjust',['as'=>'admin.system.adjust','uses'=>'SystemController@adjust']);

/*首页*/
Route::resource('index', 'IndexController', ['only' => ['index'],'as'=>'admin']);
Route::get('index/sidebar',['as'=>'sidebar','uses'=>'IndexController@sidebar']);

/*权限管理*/
Route::resource('permission', 'PermissionController',['except' => ['show'],'as'=>'admin']);

/*角色管理*/
Route::resource('role', 'RoleController',['except' => ['show'],'as'=>'admin']);
Route::post('role/permission',['as'=>'admin.role.permission','uses'=>'RoleController@permission']);

/*用户删除*/
Route::post('user/destroy',['as'=>'admin.user.destroy','uses'=>'UserController@destroy']);
/*用户审核*/
Route::post('user/verify',['as'=>'admin.user.verify','uses'=>'UserController@verify']);
/*用户管理*/
Route::resource('user', 'UserController',['except' => ['show','destroy'],'as'=>'admin']);
/*用户经历*/
Route::get('user/item/info',['as'=>'admin.user.item.info','uses'=>'UserController@itemInfo']);
/*用户经历修改或新增*/
Route::post('user/item/store',['as'=>'admin.user.item.store','uses'=>'UserController@storeItemInfo']);
/*用户经历删除*/
Route::post('user/item/destroy',['as'=>'admin.user.item.destroy','uses'=>'UserController@destroyItemInfo']);
/*用户简历*/
Route::get('user/resume/info',['as'=>'admin.user.resume.info','uses'=>'UserController@resumeInfo']);

/*认证管理*/
Route::post('authentication/destroy',['as'=>'admin.authentication.destroy','uses'=>'AuthenticationController@destroy']);
Route::post('authentication/verify',['as'=>'admin.authentication.verify','uses'=>'AuthenticationController@verify']);
/*修改分类核*/
Route::post('authentication/changeCategories',['as'=>'admin.authentication.changeCategories','uses'=>'AuthenticationController@changeCategories']);
Route::resource('authentication', 'AuthenticationController',['except' => ['show','destroy'],'as'=>'admin']);


/*注册设置*/
Route::any('setting/register',['as'=>'admin.setting.register','uses'=>'SettingController@register']);
/*积分设置*/
Route::any('setting/credits',['as'=>'admin.setting.credits','uses'=>'SettingController@credits']);

/*问答设置*/
Route::any('setting/answer',['as'=>'admin.setting.answer','uses'=>'SettingController@answer']);

/*关于我们设置*/
Route::any('setting/aboutus',['as'=>'admin.setting.aboutus','uses'=>'SettingController@aboutus']);

/*常见问题设置*/
Route::any('setting/help',['as'=>'admin.setting.help','uses'=>'SettingController@appHelp']);

/*提问帮助设置*/
Route::any('setting/qahelp',['as'=>'admin.setting.qahelp','uses'=>'SettingController@qaHelp']);

/*财务管理*/
Route::resource('credit', 'CreditController',['except' => ['show'],'as'=>'admin']);
Route::group(['namespace'=>'Finance'],function(){
    /*提现管理*/
    Route::get('withdraw/index',['as'=>'admin.finance.withdraw.index','uses'=>'WithdrawController@index']);
    Route::post('withdraw/verify',['as'=>'admin.finance.withdraw.verify','uses'=>'WithdrawController@verify']);
    Route::post('withdraw/verify_offline',['as'=>'admin.finance.withdraw.verify_offline','uses'=>'WithdrawController@verifyOffline']);


    Route::get('finance/setting/index',['as'=>'admin.finance.setting.index','uses'=>'SettingController@index']);
    Route::post('finance/setting/index',['as'=>'admin.finance.setting.index','uses'=>'SettingController@index']);

    //结算管理
    Route::get('settlement/index',['as'=>'admin.finance.settlement.index','uses'=>'SettlementController@index']);
    Route::post('settlement/destroy',['as'=>'admin.finance.settlement.destroy','uses'=>'SettlementController@destroy']);
    Route::post('settlement/verify',['as'=>'admin.finance.settlement.verify','uses'=>'SettlementController@verify']);
    Route::post('settlement/doitnow',['as'=>'admin.finance.settlement.doitnow','uses'=>'SettlementController@doitnow']);

});


/*问题删除*/
Route::post('question/destroy',['as'=>'admin.question.destroy','uses'=>'QuestionController@destroy']);
/*修改分类核*/
Route::post('question/changeCategories',['as'=>'admin.question.changeCategories','uses'=>'QuestionController@changeCategories']);
/*问题审核*/
Route::post('question/verify',['as'=>'admin.question.verify','uses'=>'QuestionController@verify']);
/*问题管理*/
Route::resource('question', 'QuestionController',['only' => ['index','edit','update'],'as'=>'admin']);


/*回答删除*/
Route::post('answer/destroy',['as'=>'admin.answer.destroy','uses'=>'AnswerController@destroy']);
/*回答审核*/
Route::post('answer/verify',['as'=>'admin.answer.verify','uses'=>'AnswerController@verify']);
/*回答管理*/
Route::resource('answer', 'AnswerController',['only' => ['index','edit','update'],'as'=>'admin']);

/*文章删除*/
Route::post('article/destroy',['as'=>'admin.article.destroy','uses'=>'ArticleController@destroy']);
/*文章审核*/
Route::post('article/verify',['as'=>'admin.article.verify','uses'=>'ArticleController@verify']);
/*修改分类核*/
Route::post('article/changeCategories',['as'=>'admin.article.changeCategories','uses'=>'ArticleController@changeCategories']);
/*文章管理*/
Route::resource('article', 'ArticleController',['only' => ['index','edit','update'],'as'=>'admin']);


/*评论删除*/
Route::post('comment/destroy',['as'=>'admin.comment.destroy','uses'=>'CommentController@destroy']);
/*评论审核*/
Route::post('comment/verify',['as'=>'admin.comment.verify','uses'=>'CommentController@verify']);
/*评论管理*/
Route::resource('comment', 'CommentController',['only' => ['index','edit','update'],'as'=>'admin']);

/*标签删除*/
Route::post('tag/destroy',['as'=>'admin.tag.destroy','uses'=>'TagController@destroy']);
/*修改分类核*/
Route::post('tag/changeCategories',['as'=>'admin.tag.changeCategories','uses'=>'TagController@changeCategories']);

/*标签审核*/
Route::post('tag/verify',['as'=>'admin.tag.verify','uses'=>'TagController@verify']);
/*标签管理*/
Route::resource('tag', 'TagController',['except' => ['show','destroy'],'as'=>'admin']);


/*分类管理*/
Route::resource('category', 'CategoryController',['except' => ['show'],'as'=>'admin']);


/*公告管理*/
Route::resource('notice', 'NoticeController',['except' => ['show'],'as'=>'admin']);

/*首页推荐*/
Route::resource('recommendation', 'RecommendationController',['except' => ['show'],'as'=>'admin']);



/*工具管理*/
Route::match(['get','post'],'tool/clearCache',['as'=>'admin.tool.clearCache','uses'=>'ToolController@clearCache']);
Route::post('tool/sendTestEmail',['as'=>'admin.tool.sendTestEmail','uses'=>'ToolController@sendTestEmail']);

/*XunSearch索引管理*/
Route::get("xunSearch/clear",['as'=>'admin.xunSearch.clear','uses'=>'XunSearchController@clear']);
Route::get("xunSearch/rebuild",['as'=>'admin.xunSearch.rebuild','uses'=>'XunSearchController@rebuild']);

/*首页问答推荐*/
Route::resource('recommendQa', 'RecommendQaController',['except' => ['show'],'as'=>'admin.operate']);

/*首页阅读推荐*/
Route::resource('recommendRead', 'RecommendReadController',['except' => ['show'],'as'=>'admin.operate']);

/*刷新首页专家推荐*/
Route::get('recommendExpert/refresh',['as'=>'admin.operate.recommendExpert.refresh','uses'=>'OperateController@refreshExpert']);

/*app版本管理*/
Route::get('version/index',['as'=>'admin.appVersion.index','uses'=>'VersionController@index']);
Route::get('version/create',['as'=>'admin.appVersion.create','uses'=>'VersionController@create']);
Route::post('version/store',['as'=>'admin.appVersion.store','uses'=>'VersionController@store']);
Route::get('version/edit/{id}',['as'=>'admin.appVersion.edit','uses'=>'VersionController@edit'])->where(['id'=>'[0-9]+']);
Route::post('version/update',['as'=>'admin.appVersion.update','uses'=>'VersionController@update']);
Route::post('version/destroy',['as'=>'admin.appVersion.destroy','uses'=>'VersionController@destroy']);
Route::post('version/verify',['as'=>'admin.appVersion.verify','uses'=>'VersionController@verify']);


/*邀请码管理*/
Route::get('rgcode/index',['as'=>'admin.operate.rgcode.index','uses'=>'RegistrationCodeController@index']);
Route::get('rgcode/create',['as'=>'admin.operate.rgcode.create','uses'=>'RegistrationCodeController@create']);
Route::post('rgcode/store',['as'=>'admin.operate.rgcode.store','uses'=>'RegistrationCodeController@store']);
Route::get('rgcode/edit/{id}',['as'=>'admin.operate.rgcode.edit','uses'=>'RegistrationCodeController@edit'])->where(['id'=>'[0-9]+']);
Route::post('rgcode/update',['as'=>'admin.operate.rgcode.update','uses'=>'RegistrationCodeController@update']);
Route::post('rgcode/destroy',['as'=>'admin.operate.rgcode.destroy','uses'=>'RegistrationCodeController@destroy']);
Route::post('rgcode/verify',['as'=>'admin.operate.rgcode.verify','uses'=>'RegistrationCodeController@verify']);

//企业管理
Route::group(['prefix' => 'company','namespace'=>'Company'], function() {
    //认证列表
    Route::get('index',['as'=>'admin.company.index','uses'=>'CompanyController@index']);
    Route::post('destroy',['as'=>'admin.company.destroy','uses'=>'CompanyController@destroy']);
    Route::post('verify',['as'=>'admin.company.verify','uses'=>'CompanyController@verify']);

});

//项目需求管理
Route::group(['prefix' => 'project','namespace'=>'Project'], function() {
    Route::get('index',['as'=>'admin.project.index','uses'=>'ProjectController@index']);
    Route::get('detail',['as'=>'admin.project.detail','uses'=>'ProjectController@detail']);
    Route::post('destroy',['as'=>'admin.project.destroy','uses'=>'ProjectController@destroy']);
    Route::post('verify',['as'=>'admin.project.verify','uses'=>'ProjectController@verify']);

});

//活动管理
Route::group(['prefix' => 'activity','namespace'=>'Activity'], function() {
    Route::get('config',['as'=>'admin.activity.config','uses'=>'ConfigController@index']);
    Route::post('config',['as'=>'admin.activity.config','uses'=>'ConfigController@index']);

    Route::get('coupon',['as'=>'admin.activity.coupon','uses'=>'CouponController@index']);

});

//推送管理
Route::group(['prefix' => 'push'], function() {
    Route::get('index',['as'=>'admin.operate.pushNotice.index','uses'=>'PushNoticeController@index']);
    Route::get('create',['as'=>'admin.operate.pushNotice.create','uses'=>'PushNoticeController@create']);
    Route::get('edit/{id}',['as'=>'admin.operate.pushNotice.edit','uses'=>'PushNoticeController@edit'])->where(['id'=>'[0-9]+']);
    Route::post('update',['as'=>'admin.operate.pushNotice.update','uses'=>'PushNoticeController@update']);
    Route::post('verify',['as'=>'admin.operate.pushNotice.verify','uses'=>'PushNoticeController@verify']);
    Route::post('testPush',['as'=>'admin.operate.pushNotice.test','uses'=>'PushNoticeController@testPush']);
    Route::post('destroy',['as'=>'admin.operate.pushNotice.destroy','uses'=>'PushNoticeController@destroy']);
});

//日志查看
Route::get('loginLog',['as'=>'admin.logger.login','uses'=>'LoggerController@loginLog']);
Route::get('sysLogs', ['as'=>'admin.logger.system','uses'=>'\Rap2hpoutre\LaravelLogViewer\LogViewerController@index']);