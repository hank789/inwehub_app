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

/*oauth2.0*/
Route::any('setting/oauth',['as'=>'admin.setting.oauth','uses'=>'SettingController@oauth']);

/*财务管理*/
Route::resource('credit', 'CreditController',['except' => ['show'],'as'=>'admin']);
Route::group(['namespace'=>'Finance'],function(){
    /*提现管理*/
    Route::get('withdraw/index',['as'=>'admin.finance.withdraw.index','uses'=>'WithdrawController@index']);
    Route::post('withdraw/verify',['as'=>'admin.finance.withdraw.verify','uses'=>'WithdrawController@verify']);

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

/*商品管理*/
Route::resource('goods', 'GoodsController',['except' => ['show'],'as'=>'admin']);
/*商品兑换*/
Route::resource('exchange', 'ExchangeController',['except' => ['show'],'as'=>'admin']);
Route::get('exchange/{id}/{status}',['as'=>'admin.exchange.changeStatus','uses'=>'ExchangeController@changeStatus'])->where(['id'=>'[0-9]+','status'=>'(success|failed)']);

/*友情链接*/
Route::resource('friendshipLink', 'FriendshipLinkController',['except' => ['show'],'as'=>'admin']);

/*工具管理*/
Route::match(['get','post'],'tool/clearCache',['as'=>'admin.tool.clearCache','uses'=>'ToolController@clearCache']);
Route::post('tool/sendTestEmail',['as'=>'admin.tool.sendTestEmail','uses'=>'ToolController@sendTestEmail']);

/*XunSearch索引管理*/
Route::get("xunSearch/clear",['as'=>'admin.xunSearch.clear','uses'=>'XunSearchController@clear']);
Route::get("xunSearch/rebuild",['as'=>'admin.xunSearch.rebuild','uses'=>'XunSearchController@rebuild']);

/*首页运营数据管理*/
Route::any('operate/home_data',['as'=>'admin.operate.home_data','uses'=>'OperateController@homeData']);

/*首页问答推荐*/
Route::resource('recommendQa', 'RecommendQaController',['except' => ['show'],'as'=>'admin.operate']);

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


/*inwehub管理*/
Route::group(['prefix' => 'inwehub','namespace'=>'Inwehub'],function(){
    /*文章创建*/
    Route::get('topic/create',['as'=>'admin.inwehub.topic.create','uses'=>'TopicController@create']);
    Route::post('topic/store',['as'=>'admin.inwehub.topic.store','uses'=>'TopicController@store']);
    Route::get('topic/edit/{id}',['as'=>'admin.inwehub.topic.edit','uses'=>'TopicController@edit'])->where(['id'=>'[0-9]+']);
    Route::post('topic/update',['as'=>'admin.inwehub.topic.update','uses'=>'TopicController@update']);
    Route::post('topic/news/update',['as'=>'admin.inwehub.topic.news.update','uses'=>'TopicController@topicNews']);
    Route::post('topic/loadNews',['as'=>'admin.inwehub.topic.loadnews','uses'=>'TopicController@loadNews']);

    /*文章删除*/
    Route::post('topic/destroy',['as'=>'admin.inwehub.topic.destroy','uses'=>'TopicController@destroy']);
    /*文章审核*/
    Route::post('topic/verify',['as'=>'admin.inwehub.topic.verify','uses'=>'TopicController@verify']);
    /*文章管理*/
    Route::resource('topic', 'TopicController',['only' => ['index','edit'],'as'=>'admin.inwehub']);

    /*新闻管理*/
    Route::get('news/create',['as'=>'admin.inwehub.news.create','uses'=>'NewsController@create']);
    Route::post('news/store',['as'=>'admin.inwehub.news.store','uses'=>'NewsController@store']);
    Route::get('news/edit/{id}',['as'=>'admin.inwehub.news.edit','uses'=>'NewsController@edit'])->where(['id'=>'[0-9]+']);
    Route::post('news/update',['as'=>'admin.inwehub.news.update','uses'=>'NewsController@update']);
    Route::post('news/destroy',['as'=>'admin.inwehub.news.destroy','uses'=>'NewsController@destroy']);
    Route::post('news/verify',['as'=>'admin.inwehub.news.verify','uses'=>'NewsController@verify']);

    Route::resource('news', 'NewsController',['only' => ['index','edit'],'as'=>'admin.inwehub']);

    /*数据源管理*/
    Route::get('feeds/create',['as'=>'admin.inwehub.feeds.create','uses'=>'FeedsController@create']);
    Route::post('feeds/store',['as'=>'admin.inwehub.feeds.store','uses'=>'FeedsController@store']);
    Route::get('feeds/edit/{id}',['as'=>'admin.inwehub.feeds.edit','uses'=>'FeedsController@edit'])->where(['id'=>'[0-9]+']);
    Route::post('feeds/update',['as'=>'admin.inwehub.feeds.update','uses'=>'FeedsController@update']);
    Route::post('feeds/destroy',['as'=>'admin.inwehub.feeds.destroy','uses'=>'FeedsController@destroy']);
    Route::get('feeds/sync',['as'=>'admin.inwehub.feeds.sync','uses'=>'FeedsController@sync']);
    Route::post('feeds/verify',['as'=>'admin.inwehub.feeds.verify','uses'=>'FeedsController@verify']);


    Route::resource('feeds', 'FeedsController',['only' => ['index','edit'],'as'=>'admin.inwehub']);
    /*微信公众号*/
    Route::get('wechat/author/create',['as'=>'admin.inwehub.wechat.author.create','uses'=>'WechatController@createAuthor']);
    Route::post('wechat/author/store',['as'=>'admin.inwehub.wechat.author.store','uses'=>'WechatController@storeAuthor']);
    Route::get('wechat/author/index',['as'=>'admin.inwehub.wechat.author.index','uses'=>'WechatController@indexAuthor']);
    Route::post('wechat/author/destroy',['as'=>'admin.inwehub.wechat.author.destroy','uses'=>'WechatController@destroyAuthor']);
    Route::post('wechat/author/verify',['as'=>'admin.inwehub.wechat.author.verify','uses'=>'WechatController@verifyAuthor']);
    Route::get('wechat/author/sync',['as'=>'admin.inwehub.wechat.author.sync','uses'=>'WechatController@sync']);

    /*微信公众号文章管理*/
    Route::get('wechat/article/index',['as'=>'admin.inwehub.wechat.article.index','uses'=>'WechatController@indexArticle']);
    Route::post('wechat/article/destroy',['as'=>'admin.inwehub.wechat.article.destroy','uses'=>'WechatController@destroyArticle']);
    Route::post('wechat/article/verify',['as'=>'admin.inwehub.wechat.article.verify','uses'=>'WechatController@verifyArticle']);


});
