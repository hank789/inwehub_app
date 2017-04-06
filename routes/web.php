<?php

/*installer*/
Route::Group(['namespace'=>'Installer','middleware'=>'installer'],function(){
    Route::get('/install',['as'=>'website.installer.welcome','uses'=>'InstallerController@welcome']);
    Route::get('/install/requirement',['as'=>'website.installer.requirement','uses'=>'InstallerController@requirement']);
    Route::any('/install/config',['as'=>'website.installer.config','uses'=>'InstallerController@config']);
    Route::get('/install/initDB',['as'=>'website.installer.initDB','uses'=>'InstallerController@initDB']);
    Route::any('/install/website',['as'=>'website.installer.website','uses'=>'InstallerController@website']);
    Route::get('/install/finished',['as'=>'website.installer.finished','uses'=>'InstallerController@finished']);
});


/*首页*/
Route::get('/',['as'=>'website.index','middleware'=>'auth','uses'=>'IndexController@index']);

Route::get('/callback',['as'=>'website.index.callback','uses'=>'IndexController@callback']);

/*问答*/
Route::get('/questions/{category_name?}/{filter?}',['as'=>'website.ask','uses'=>'IndexController@ask'])->where(['filter'=>'(newest|hottest|reward|unAnswered)']);

/*标签*/
Route::get('/topics/{category_name?}',['as'=>'website.topic','uses'=>'IndexController@topic']);

/*文章*/
Route::get('/articles/{category_name?}/{filter?}',['as'=>'website.blog','uses'=>'IndexController@blog'])->where(['filter'=>'(recommended|newest|hottest)']);

/*用户*/
Route::get('/users',['as'=>'website.user','uses'=>'IndexController@user']);

/*experts*/
Route::get('/experts/{categorySlug?}/{provinceId?}',['as'=>'website.experts','uses'=>'IndexController@experts']);


/*积分商城*/
Route::get('/shop',['as'=>'website.shop','uses'=>'IndexController@shop']);

/*sitemap*/
Route::get('/sitemap',['as'=>'website.sitemap','uses'=>'SiteMapController@index']);




/*用户账号管理，包含用户登录注册等操作*/
Route::Group(['namespace'=>'Account'],function(){
    Route::match(['get','post'],'login',['as'=>'auth.user.login','uses'=>'UserController@login']);
    Route::match(['get','post'],'register',['as'=>'auth.user.register','uses'=>'UserController@register']);
    Route::get('logout',['as'=>'auth.user.logout','uses'=>'UserController@logout']);
    /*密码找回*/
    Route::match(['get','post'],'forgetPassword',['as'=>'auth.user.forgetPassword','uses'=>'UserController@forgetPassword']);
    Route::match(['get','post'],'findPassword/{token}',['as'=>'auth.user.findPassword','uses'=>'UserController@findPassword']);

    /*用户auth2.0*/
    Route::get('oauth/{type}/login',['as'=>'auth.oauth.login','uses'=>'OauthController@login'])->where(['type'=>'(qq|weibo|weixin|weixinweb)']);
    Route::get('oauth/{type}/callback',['as'=>'auth.oauth.callback','uses'=>'OauthController@callback'])->where(['type'=>'(qq|weibo|weixin|weixinweb)']);
    Route::get('oauth/register/{auth_id}',['as'=>'auth.oauth.profile','uses'=>'OauthController@profile']);
    Route::post('oauth/register',['as'=>'auth.oauth.register','uses'=>'OauthController@register']);


    /*动态*/
    Route::Group(['middleware'=>'auth'],function(){
        Route::get('doings',['as'=>'auth.doing.index','uses'=>'DoingsController@index']);
        /*用户空间首页*/
        Route::get('people/{user_id}',['as'=>'auth.space.index','uses'=>'SpaceController@index'])->where(['user_id'=>'[0-9]+']);
        /*我的提问*/
        Route::get('people/{user_id}/questions',['as'=>'auth.space.questions','uses'=>'SpaceController@questions'])->where(['user_id'=>'[0-9]+']);
        /*我的回答*/
        Route::get('people/{user_id}/answers',['as'=>'auth.space.answers','uses'=>'SpaceController@answers'])->where(['user_id'=>'[0-9]+']);
        /*我的文章*/
        Route::get('people/{user_id}/articles',['as'=>'auth.space.articles','uses'=>'SpaceController@articles'])->where(['user_id'=>'[0-9]+']);

        /*我的粉丝*/
        Route::get('people/{user_id}/followers',['as'=>'auth.space.followers','uses'=>'SpaceController@followers'])->where(['user_id'=>'[0-9]+']);
        /*我的关注*/
        Route::get('people/{user_id}/followed/{source_type}',['as'=>'auth.space.attentions','uses'=>'SpaceController@attentions'])->where(['user_id'=>'[0-9]+','source_type'=>'(questions|tags|users)']);
        /*我的收藏*/
        Route::get('people/{user_id}/collected/{source_type}',['as'=>'auth.space.collections','uses'=>'SpaceController@collections'])->where(['user_id'=>'[0-9]+','source_type'=>'(questions|articles)']);

        /*我的金币*/
        Route::get('people/{user_id}/coins',['as'=>'auth.space.coins','uses'=>'SpaceController@coins'])->where(['user_id'=>'[0-9]+']);
        /*我的经验*/
        Route::get('people/{user_id}/credits',['as'=>'auth.space.credits','uses'=>'SpaceController@credits'])->where(['user_id'=>'[0-9]+']);

        /*全局搜索*/
        Route::any('search/{filter?}',['as'=>'auth.search.index','uses'=>'SearchController@index'])->where(['filter'=>'(all|questions|articles|tags|users)']);

        /*邮箱token验证*/
        Route::get('email/{action}/{token}',['as'=>'auth.email.verifyToken','uses'=>'EmailController@verifyToken'])->where(['action'=>'(register|verify)']);


        /*用户排行榜*/

        /*财富榜*/
        Route::get('top/coins',['as'=>'auth.top.coins','uses'=>'TopController@coins']);

        /*回答榜*/
        Route::get('top/answers',['as'=>'auth.top.answers','uses'=>'TopController@answers']);

        /*文章榜*/
        Route::get('top/articles',['as'=>'auth.top.articles','uses'=>'TopController@articles']);

        Route::get('email/sendToken',['as'=>'auth.email.sendToken','uses'=>'EmailController@sendToken']);

        Route::get('oauth/{type}/unbind',['as'=>'auth.oauth.unbind','uses'=>'OauthController@unbind']);
        /*用户个人信息修改*/
        /*Route::controller('profile','ProfileController', [
            'anyBase'     => 'auth.profile.base',
            'postAvatar'  => 'auth.profile.avatar',
            'anyPassword' =>'auth.profile.password',
            'anyEmail'    =>'auth.profile.email',
            'anyOauth'    =>'auth.profile.oauth',
            'anyNotification' =>'auth.profile.notification',
        ]);*/
        Route::get('profile/base',['as'=>'auth.profile.base','uses'=>'ProfileController@anyBase']);
        Route::post('profile/avatar',['as'=>'auth.profile.avatar','uses'=>'ProfileController@postAvatar']);
        Route::get('profile/password',['as'=>'auth.profile.password','uses'=>'ProfileController@anyPassword']);
        Route::get('profile/email',['as'=>'auth.profile.email','uses'=>'ProfileController@anyEmail']);
        Route::get('profile/oauth',['as'=>'auth.profile.oauth','uses'=>'ProfileController@anyOauth']);
        Route::get('profile/notification',['as'=>'auth.profile.notification','uses'=>'ProfileController@anyNotification']);


        /*行家认证*/
        /*Route::controller('authentication','AuthenticationController', [
            'getIndex'     => 'auth.authentication.index',
            'anyEdit' =>'auth.authentication.edit',
            'postStore'    =>'auth.authentication.store'
        ]);*/
        Route::get('authentication/index',['as'=>'auth.authentication.index','uses'=>'AuthenticationController@getIndex']);
        Route::get('authentication/edit',['as'=>'auth.authentication.edit','uses'=>'AuthenticationController@anyEdit']);
        Route::post('authentication/store',['as'=>'auth.authentication.store','uses'=>'AuthenticationController@postStore']);


        /*我的通知*/
        /*Route::controller('notifications','NotificationController',[
            'getIndex' => 'auth.notification.index',
            'getReadAll' => 'auth.notification.readAll',
        ]);*/
        Route::get('notifications/index',['as'=>'auth.notification.index','uses'=>'NotificationController@getIndex']);
        Route::get('notifications/readAll',['as'=>'auth.notification.readAll','uses'=>'NotificationController@getReadAll']);


        /*我的私信*/
        Route::get('messages',['as'=>'auth.message.index','uses'=>'MessageController@index']);
        Route::get('message/{user_id}',['as'=>'auth.message.show','uses'=>'MessageController@show'])->where(['user_id'=>'[0-9]+']);
        Route::get('message/destroy/{id}',['as'=>'auth.message.destroy','uses'=>'MessageController@destroy'])->where(['id'=>'[0-9]+']);
        Route::get('message/destroySession/{id}',['as'=>'auth.message.destroySession','uses'=>'MessageController@destroySession'])->where(['from_user_id'=>'[0-9]+']);
        Route::post('message/store',['as'=>'auth.message.store','uses'=>'MessageController@store']);


        /*邀请我回答的问题*/
        Route::get('questionInvitation',['as'=>'auth.questionInvitation.index','uses'=>'QuestionInvitationController@index']);



        /*收藏问题、文章*/

        Route::get('collect/{source_type}/{source_id}',['as'=>'auth.collection.store','uses'=>'CollectionController@store'])->where(['source_type'=>'(question|article)','source_id'=>'[0-9]+']);

        /*关注问题、人、标签*/
        Route::get('follow/{source_type}/{source_id}',['as'=>'auth.attention.store','uses'=>'AttentionController@store'])->where(['source_type'=>'(question|tag|user)','source_id'=>'[0-9]+']);

        /*点赞*/
        Route::get('support/{source_type}/{source_id}',['as'=>'auth.support.store','uses'=>'SupportController@store'])->where(['source_type'=>'(answer|article|comment)','source_id'=>'[0-9]+']);
        Route::get('support/check/{source_type}/{source_id}',['as'=>'auth.support.check','uses'=>'SupportController@check'])->where(['source_type'=>'(answer|article|comment)','source_id'=>'[0-9]+']);

    });
});

/*前台显示部分*/
Route::Group(['namespace'=>'Ask','middleware'=>'auth'],function(){


    /*问题查看*/
    Route::get('question/{id}',['as'=>'ask.question.detail','uses'=>'QuestionController@detail'])->where(['id'=>'[0-9]+']);

    /*回答详情查看*/
    Route::get('question/{question_id}/answer/{id}',['as'=>'ask.answer.detail','uses'=>'AnswerController@detail'])->where(['id'=>'[0-9]+','question_id'=>'[0-9]+']);

    /*问题建议*/
    Route::post('question/suggest',['as'=>'ask.question.suggest','uses'=>'QuestionController@suggest']);



    /*问题创建*/
    Route::get('question/create',['as'=>'ask.question.create','uses'=>'QuestionController@create']);
    Route::post('question/store',['middleware' =>'ban.user','as'=>'ask.question.store','uses'=>'QuestionController@store']);


    /*问题修改*/
    Route::get('question/edit/{id}',['as'=>'ask.question.edit','uses'=>'QuestionController@edit'])->where(['id'=>'[0-9]+']);
    Route::post('question/update',['middleware' =>'ban.user','as'=>'ask.question.update','uses'=>'QuestionController@update']);

    /*追加悬赏*/
    Route::post('question/{id}/appendReward',['as'=>'ask.question.appendReward','uses'=>'QuestionController@appendReward'])->where(['id'=>'[0-9]+']);

    /*邀请回答*/
    Route::get('question/invite/{question_id}/{to_user_id}',['as'=>'ask.question.invite','uses'=>'QuestionController@invite'])->where(['question_id'=>'[0-9]+','to_user_id'=>'[0-9]+']);
    Route::any('question/inviteEmail/{question_id}',['as'=>'ask.question.inviteEmail','uses'=>'QuestionController@inviteEmail'])->where(['question_id'=>'[0-9]+']);
    Route::get('question/{question_id}/invitations/{type}',['as'=>'ask.question.invitations','uses'=>'QuestionController@invitations'])->where(['question_id'=>'[0-9]+','type'=>'(part|all)']);

    /*采纳回答*/
    Route::get('answer/adopt/{id}',['as'=>'ask.answer.adopt','uses'=>'AnswerController@adopt'])->where(['id'=>'[0-9]+']);

    /*回答保存*/
    Route::post('answer/store',['as'=>'ask.answer.store','uses'=>'AnswerController@store']);
    /*回答编辑页面显示*/
    Route::get('answer/edit/{id}',['as'=>'ask.answer.edit','uses'=>'AnswerController@edit'])->where(['id'=>'[0-9]+']);
    /*回答保存*/
    Route::post('answer/update/{id}',['as'=>'ask.answer.update','uses'=>'AnswerController@update'])->where(['id'=>'[0-9]+']);

    /*评论添加*/
    Route::post('comment/store',['middleware' =>'ban.user','as'=>'ask.comment.store','uses'=>'CommentController@store']);

    /*标签首页*/
    Route::get('topic/{id}/{source_type?}',['as'=>'ask.tag.index','uses'=>'TagController@index'])->where(['id'=>'[0-9]+','source_type'=>'(questions|articles|details)']);

    /*加载评论*/
    Route::get('{source_type}/{source_id}/comments',['as'=>'ask.comment.show','uses'=>'CommentController@show'])->where(['source_type'=>'(question|answer|article)','source_id'=>'[0-9]+']);

});


/*文章模块*/
Route::Group(['namespace'=>'Blog','middleware'=>'auth'],function(){

    /*文章查看*/
    Route::get('article/{id}',['as'=>'blog.article.detail','uses'=>'ArticleController@show'])->where(['id'=>'[0-9]+']);

    /*文章创建*/
    Route::get('article/create',['as'=>'blog.article.create','uses'=>'ArticleController@create']);
    Route::post('article/store',['middleware' =>'ban.user','as'=>'blog.article.store','uses'=>'ArticleController@store']);
    Route::get('article/edit/{id}',['as'=>'blog.article.edit','uses'=>'ArticleController@edit'])->where(['id'=>'[0-9]+']);
    Route::post('article/update',['middleware' =>'ban.user','as'=>'blog.article.update','uses'=>'ArticleController@update']);

});

/*商城模块*/
Route::Group(['namespace'=>'Shop','middleware'=>'auth'],function(){

    /*商品详情查看*/
    Route::get('goods/{id}',['as'=>'shop.goods.detail','uses'=>'GoodsController@show'])->where(['id'=>'[0-9]+']);

    /*兑换礼品*/
    Route::POST('goods/exchange',['as'=>'shop.goods.exchange','uses'=>'GoodsController@exchange']);

    /*我的商城兑换记录*/
    Route::get('exchanges',['as'=>'shop.exchange.index','uses'=>'ExchangeController@index']);

});


/*后台管理部分处理*/

Route::Group(['prefix'=>'admin','namespace'=>'Admin','middleware' =>['auth','auth.admin']],function(){
    require_once base_path('routes/admin.php');
});


/*公共ajax异步加载*/

/*加载省份城市信息*/
Route::get('ajax/loadCities/{province_id}',['as'=>'website.ajax.loadCities','uses'=>'AjaxController@loadCities'])->where(['province_id'=>'[0-9]+']);
/*加载未读通知数目*/
Route::get('ajax/unreadNotifications',['as'=>'website.ajax.unreadNotifications','uses'=>'AjaxController@unreadNotifications']);
Route::get('ajax/loadTags',['as'=>'website.ajax.loadTags','uses'=>'AjaxController@loadTags']);

Route::get('ajax/loadUsers',['middleware' =>'auth','as'=>'website.ajax.loadUsers','uses'=>'AjaxController@loadUsers']);
Route::get('ajax/loadInviteUsers',['middleware' =>'auth','as'=>'website.ajax.loadInviteUsers','uses'=>'AjaxController@loadInviteUsers']);

/*加载未读私信数目*/
Route::get('ajax/unreadMessages',['as'=>'website.ajax.unreadMessages','uses'=>'AjaxController@unreadMessages']);


Route::get('image/avatar/{avatar_name}',['as'=>'website.image.avatar','uses'=>'ImageController@avatar'])->where(['avatar_name'=>'[0-9]+_(small|middle|big|origin).jpg']);
Route::get('image/show/{image_name}',['as'=>'website.image.show','uses'=>'ImageController@show']);

Route::Group(['middleware'=>'auth'],function(){
    Route::post('image/upload',['as'=>'website.image.upload','uses'=>'ImageController@upload']);
});
