<?php
/**
 * @author: wanghui
 * @date: 2017/4/6 下午3:12
 * @email: wanghui@yonglibao.com
 */

//app首页
Route::post('home','IndexController@home')->middleware('jwt.auth');
Route::post('comment/myList','IndexController@myCommentList')->middleware('jwt.auth');

//精选推荐列表
Route::post('recommendRead','IndexController@recommendRead')->middleware('jwt.auth');


//登陆注册认证类
Route::group(['prefix' => 'auth','namespace'=>'Account'], function() {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('refresh', ['uses'=>'AuthController@refreshToken']);

    Route::post('forgot', 'AuthController@forgetPassword');
    Route::post('sendPhoneCode', 'AuthController@sendPhoneCode');

    Route::post('logout', 'AuthController@logout')->middleware('jwt.auth');

    //等级权限判断
    Route::post('checkUserLevel','AuthController@checkUserLevel')->middleware('jwt.auth');

    //微信公众号注册验证
    Route::post('wxgzh/check_rg', 'AuthController@checkWeiXinGzh');
    //微信公众号注册
    Route::post('wxgzh/register', 'AuthController@registerWeiXinGzh');
    //微信小程序注册
    Route::post('weapp/register', 'AuthController@registerWeapp')->middleware(['jwt.weappConfig','jwt.weappAuth']);
});

Route::group(['namespace'=>'Share'], function() {
    //微信分享
    Route::any('share/wechat/jssdk','WechatController@jssdk');
    Route::post('share/wechat/success','WechatController@shareSuccess')->middleware('jwt.auth');

});

Route::group(['namespace'=>'Account'], function() {
    //用户个人名片
    Route::any('profile/resumeInfo','ProfileController@resumeInfo');
});

//榜单
Route::group(['middleware' => ['jwt.auth','ban.user'], 'prefix'=>'rank'], function() {
    //用户积分数据
    Route::post('userInfo','RankController@userInfo');
    //用户贡献榜
    Route::post('userContribution','RankController@userContribution');
    //用户成长榜
    Route::post('userGrowth','RankController@userGrowth');
    //用户邀请榜
    Route::post('userInvitation','RankController@userInvitation');
    //用户点赞榜
    Route::post('userUpvotes','RankController@userUpvotes');
});

//用户oauth
Route::post('oauth/{type}/callback',['uses'=>'Account\OauthController@callback'])->where(['type'=>'(weixinapp|weixin_gzh)']);

//用户信息
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Account'], function() {
    //用户信息
    Route::post('profile/info','ProfileController@info');

    //修改用户头像
    Route::post('profile/updateAvatar','ProfileController@postAvatar');
    //用户修改密码
    Route::post('profile/password','ProfileController@updatePassword');
    //用户修改基本信息
    Route::post('profile/update','ProfileController@update');
    //添加用户擅长标签
    Route::post('profile/addSkillTag','ProfileController@addSkillTag');
    //删除用户擅长标签
    Route::post('profile/delSkillTag','ProfileController@delSkillTag');


    //上传简历
    Route::post('profile/uploadResume','ProfileController@uploadResume');

    //隐私详情
    Route::post('profile/privacy/info','ProfileController@privacyInfo');
    //隐私设置
    Route::post('profile/privacy/update','ProfileController@privacyUpdate');

    //资金明细
    Route::post('account/money_log','ProfileController@moneyLog');
    //个人钱包
    Route::post('account/wallet','ProfileController@wallet');


    //专家认证申请
    Route::post('expert/apply','ExpertController@apply');
    //推荐专家
    Route::post('expert/recommend','ExpertController@recommend');
    //专家审核情况
    Route::post('expert/info','ExpertController@info');


    //教育经历
    Route::post('account/edu/store','EduController@store');
    Route::post('account/edu/update','EduController@update');
    Route::post('account/edu/destroy','EduController@destroy');
    Route::post('account/edu/list','EduController@showList');


    //工作经历
    Route::post('account/job/store','JobController@store');
    Route::post('account/job/update','JobController@update');
    Route::post('account/job/destroy','JobController@destroy');
    Route::post('account/job/list','JobController@showList');

    //培训经历
    Route::post('account/train/store','TrainController@store');
    Route::post('account/train/update','TrainController@update');
    Route::post('account/train/destroy','TrainController@destroy');
    Route::post('account/train/list','TrainController@showList');

    //项目经历
    Route::post('account/project/store','ProjectController@store');
    Route::post('account/project/update','ProjectController@update');
    Route::post('account/project/destroy','ProjectController@destroy');
    Route::post('account/project/list','ProjectController@showList');

    /*关注问题、人、标签*/
    Route::post('follow/{source_type}',['uses'=>'FollowController@store'])->where(['source_type'=>'(question|tag|user)']);
    //批量关注
    Route::post('follow/batchUser',['uses'=>'FollowController@batchUser']);
    //批量关注标签
    Route::post('follow/batchTags',['uses'=>'FollowController@batchTags']);
    //批量关注问题
    Route::post('follow/batchQuestions',['uses'=>'FollowController@batchQuestions']);
    //推荐关注用户
    Route::post('follow/recommendUserList',['uses'=>'FollowController@recommendUserList']);
    /*我的关注*/
    Route::post('followed/{source_type}',['uses'=>'FollowController@attentions'])->where(['source_type'=>'(questions|tags|users)']);

    Route::post('followed/searchUsers',['uses'=>'FollowController@searchFollowedUser']);

    //关注标签的用户列表
    Route::post('followed/tagUsers',['uses'=>'FollowController@tagUsers']);


    //收藏
    Route::post('collect/{source_type}',['uses'=>'CollectionController@store'])->where(['source_type'=>'(question|answer)']);
    //收藏列表
    Route::post('collected/{source_type}',['uses'=>'CollectionController@collectList'])->where(['source_type'=>'(questions|answers|readhubSubmission)']);

    //关注我的
    Route::post('follow_my/users',['uses'=>'FollowController@followMe']);

    Route::post('im/getWhisperRoom','MessageController@getWhisperRoom');

});
//IM
Route::post('im/message-store','Account\MessageController@store');
Route::post('im/messages','Account\MessageController@getMessages');
Route::post('im/getRoom','Account\MessageController@getRoom');
Route::post('im/createRoom','Account\MessageController@createRoom');

//问答模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Ask'], function() {
    //回答反馈
    Route::post('answer/feedback','AnswerController@feedback');
    //我的回答列表
    Route::post('answer/myList','AnswerController@myList');
    //我的提问列表
    Route::post('question/myList','QuestionController@myList');
    //拒绝回答
    Route::post('question/rejectAnswer','QuestionController@rejectAnswer');
    //提问请求
    Route::post('question/request','QuestionController@request');
    //新建回答
    Route::post('answer/store','AnswerController@store');
    //修改回答
    Route::post('answer/update','AnswerController@update');
    //新建提问
    Route::post('question/store','QuestionController@store');
    //查看点评
    Route::post('answer/feedbackInfo','AnswerController@feedbackInfo');
    //问题详情
    //Route::post('question/info','QuestionController@info');
    //付费围观
    Route::post('answer/payforview','AnswerController@payForView');
    //专业问答-热门问答
    Route::post('question/majorHot','QuestionController@majorHot');
    //邀请回答
    Route::post('question/inviteAnswer','QuestionController@inviteAnswer');
    //邀请列表
    Route::post('question/inviterList','QuestionController@inviterList');
    //相关问题
    Route::post('question/relatedQuestion','QuestionController@relatedQuestion');

    //推荐用户问题
    Route::post('question/recommendUser','QuestionController@recommendUserQuestions');

    //一键推荐邀请回答
    Route::post('question/recommendInviterList','QuestionController@recommendInviterList');


    //问答留言
    Route::post('answer/comment','AnswerController@comment');
    //回答暂存
    Route::post('answer/saveDraft','AnswerController@saveDraft');
    //取得回答暂存内容
    Route::post('answer/getDraft','AnswerController@getDraft');
    //我的围观
    Route::post('answer/myOnlookList','AnswerController@myOnlookList');
});
//问题详情
Route::post('question/info', 'Ask\QuestionController@info');
//专业问答-推荐问答列表
Route::post('question/majorList','Ask\QuestionController@majorList');
//互动问答-问答列表
Route::post('question/commonList','Ask\QuestionController@commonList');
//回答详情
Route::post('answer/info','Ask\AnswerController@info');
//问题回答列表
Route::post('question/answerList','Ask\QuestionController@answerList');
//问答留言列表
Route::post('answer/commentList','Ask\AnswerController@commentList');
//问答社区
Route::post('question/list','Ask\QuestionController@questionList');

//任务模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Task'], function() {
    //我的任务列表
    Route::post('task/myList','TaskController@myList');

});
//搜索模块
Route::group(['middleware' => ['jwt.auth','ban.user']], function() {
    //搜索用户
    Route::post('search/user','SearchController@user');
    //搜索标签
    Route::post('search/tag','SearchController@tag');
    //搜索文章
    Route::post('search/submission','SearchController@submission');
});
//搜索问答
Route::post('search/question','SearchController@question');

//支付
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Pay'], function() {
    //支付请求
    Route::post('pay/request','PayController@request');
    //IAP结果验证
    Route::post('pay/iap_notify','NotifyController@iapNotify');
});

//提现
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Withdraw'], function() {
    //提现请求
    Route::post('withdraw/request','WithdrawController@request');

});

//加载标签
Route::post('tags/load','TagsController@load');
//标签
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix'=>'tags'], function() {

    //标签详情
    Route::post('tagInfo','TagsController@tagInfo');
    //标签用户
    Route::post('users','TagsController@users');
    //标签问答
    Route::post('questions','TagsController@questions');
    //标签动态
    Route::post('submissions','TagsController@submissions');
    //提建议，谈工作，贺新春
    Route::post('getThreeAc','TagsController@getThreeAc');

});

//上传图片
Route::post('upload/img','ImageController@upload')->middleware('jwt.auth');

//意见反馈
Route::post('system/feedback','SystemController@feedback')->middleware('jwt.auth');

//功能预告信息收集
Route::post('system/func_zan','SystemController@funcZan')->middleware('jwt.auth');

//保存用户设备信息
Route::post('system/device','SystemController@device')->middleware('jwt.auth');

//保存用户位置信息
Route::post('system/location','SystemController@location')->middleware('jwt.auth');


//申请添加用户擅长标签
Route::post('system/applySkillTag','SystemController@applySkillTag')->middleware('jwt.auth');

//htmlToImage
Route::post('system/htmlToImage','SystemController@htmlToImage')->middleware('jwt.auth');


//检测app版本
Route::post('system/version','SystemController@appVersion');

//支付参数
Route::post('pay/config','SystemController@getPayConfig');


//获取服务条款
Route::post('system/service_register','SystemController@serviceRegister');
//获取关于我们
Route::post('system/service_about','SystemController@serviceAbout');
//获取常见问题
Route::post('system/service_help','SystemController@serviceHelp');
//获取提问帮助设置
Route::post('system/service_qa_help','SystemController@serviceQaHelp');
//获取应用市场地址
Route::post('system/app_market_url','SystemController@getAppMarketUrl')->middleware('jwt.auth');
//启动页引导
Route::post('system/boot_guide','SystemController@bootGuide');

//消息模块
Route::group(['middleware' => ['jwt.auth','ban.user']], function() {
    //阅读发现通知列表
    Route::post('notification/readhub_list','NotificationController@readhubList');
    //任务通知列表
    Route::post('notification/task_list','NotificationController@taskList');
    //公告通知列表
    Route::post('notification/notice_list','NotificationController@noticeList');
    //资金通知列表
    Route::post('notification/money_list','NotificationController@moneyList');
    //标记通知为已读
    Route::post('notification/mark_as_read','NotificationController@markAsRead');
    //统计
    Route::post('notification/count','NotificationController@count');
    //推送设置
    Route::post('notification/push/update','NotificationController@pushSettings');
    //获取推送设置
    Route::post('notification/push/info','NotificationController@getPushSettings');
});


//项目模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'project','namespace'=>'Project'], function() {
    //需求列表
    Route::post('myList','ProjectController@myList');
    //需求详情
    Route::post('info','ProjectController@info');
    //发布需求第一步
    Route::post('step_one','ProjectController@publishStepOne');
    //发布需求第二步
    Route::post('step_two','ProjectController@publishStepTwo');
    //发布需求第三步
    Route::post('step_three','ProjectController@publishStepThree');
    //发布需求第四步
    Route::post('step_four','ProjectController@publishStepFour');
    //删除项目
    Route::post('destroy','ProjectController@destroy');

});

//活动模块
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'activity','namespace'=>'Activity'], function() {
    //获取红包
    Route::post('getCoupon', 'CouponController@getCoupon');
    //活动列表
    Route::post('list', 'ActivityController@index');
    //活动回复列表
    Route::post('commentList', 'ActivityController@commentList');
    //活动报名
    Route::post('enroll', 'ActivityController@enroll');
    //活动评论
    Route::post('commentCreate', 'ActivityController@commentStore');
    //活动详情
    Route::post('detail', 'ActivityController@detail');

    //邀请注册活动页
    Route::post('inviteRegister/introduce', 'InviteController@registerIntroduce');
    //我邀请的好友
    Route::post('inviteRegister/myList', 'InviteController@myRegisterList');

    //每日签到
    Route::post('sign/daily', 'SignController@daily');
    //获取用户签到信息
    Route::post('sign/dailyInfo', 'SignController@dailyInfo');

});

//获取邀请注册者消息
Route::post('activity/inviteRegister/getInviterInfo', 'Activity\InviteController@getInviterInfo');
Route::post('activity/inviteRegister/rules', 'Activity\InviteController@inviteRules');


//企业
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'company','namespace'=>'Company'], function() {
    //申请认证
    Route::post('apply','CompanyController@apply');
    //认证信息
    Route::post('applyInfo','CompanyController@applyInfo');

    //企业服务列表
    Route::post('services','CompanyController@serviceList');
    //用户申请企业服务
    Route::post('applyService','CompanyController@applyService');

    //附近企业搜索
    Route::post('nearbySearch','CompanyController@nearbySearch');
    //企业信息
    Route::post('dataInfo','CompanyController@dataInfo');
    //企业相关人员
    Route::post('dataPeople','CompanyController@dataPeople');
    //申请企业相关人员
    Route::post('applyDataPeople','CompanyController@applyDataPeople');
    //申请添加企业
    Route::post('applyAddData','CompanyController@applyAddData');
});


//微信小程序
Route::group(['namespace'=>'Weapp','prefix' => 'weapp','middleware' => ['jwt.weappConfig']], function() {
    //获取用户微信信息
    Route::post('user/wxinfo','UserController@getWxUserInfo');
    //获取用户信息
    Route::post('user/info','UserController@getUserInfo')->middleware(['jwt.weappAuth']);
    //获取二维码
    Route::post('user/getQrCode','UserController@getQrCode');
    //获取未读消息列表
    Route::post('user/getMessageRooms','UserController@getMessageRooms')->middleware(['jwt.weappAuth']);
    //获取需求联系人消息列表
    Route::post('demand/getRooms','DemandController@getRooms')->middleware(['jwt.weappAuth']);
    //发布需求
    Route::post('demand/store','DemandController@store')->middleware(['jwt.weappAuth']);
    //修改需求
    Route::post('demand/update','DemandController@update')->middleware(['jwt.weappAuth']);
    //关闭需求
    Route::post('demand/close','DemandController@close')->middleware(['jwt.weappAuth']);
    //列表
    Route::post('demand/list','DemandController@showList')->middleware(['jwt.weappAuth']);
    //需求详情
    Route::post('demand/detail','DemandController@detail')->middleware(['jwt.weappAuth']);
});

Route::group(['middleware' => ['jwt.auth','ban.user'], 'namespace'=>'Weapp'], function() {
    //提问
    Route::post('weapp/question/store','QuestionController@store');
    //添加提问图片
    Route::post('weapp/question/add_image','QuestionController@addImage');
    //提问列表
    Route::post('weapp/question/allList','QuestionController@allList');
    //问题回复列表
    Route::post('weapp/question/loadAnswer','QuestionController@loadAnswer');


    //问题详情
    Route::post('weapp/question/info','QuestionController@info');
    //我的提问列表
    Route::post('weapp/question/myList','QuestionController@myList');
    //回答
    Route::post('weapp/answer/store','AnswerController@store');

});

//feed
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'feed'], function() {
    //首页feed列表
    Route::post('list','FeedController@index');
});

//readhub
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'readhub'], function() {
    //我的文章列表
    Route::post('mySubmission','ReadhubController@mySubmission');
});

//圈子
Route::group(['middleware' => ['jwt.auth','ban.user'],'prefix' => 'group'], function() {
    //创建圈子
    Route::post('store','GroupController@store');
    //修改圈子
    Route::post('update','GroupController@update');
    //加入圈子
    Route::post('join','GroupController@join');
    //圈子分享列表
    Route::post('submissionList','GroupController@submissionList');
    //圈子成员列表
    Route::post('members','GroupController@members');
    //圈子精华列表
    Route::post('recommendList','GroupController@recommendList');
    //圈子详情
    Route::post('detail','GroupController@detail');
    //审核通过圈子成员
    Route::post('joinAgree','GroupController@joinAgree');
    //我的圈子
    Route::post('mine','GroupController@mine');
    //拒绝通过圈子成员
    Route::post('joinReject','GroupController@joinReject');
    //退出圈子
    Route::post('quit','GroupController@quit');
});

//文章
Route::group(['middleware' => ['jwt.auth','ban.user'],'namespace'=>'Article','prefix' => 'article'], function() {
    //文章列表
    Route::post('list','HomeController@feed');
    //存储文章
    Route::post('store','SubmissionController@store');
    //推荐文章到app
    Route::post('recommend-app-submission','SubmissionController@recommendSubmission');

    //获取url标题
    Route::post('fetch-url-title','SubmissionController@getTitleAPI');
    //获取频道
    Route::post('get-categories','CategoryController@getCategories');

    //文章回复
    Route::post('comment-store','CommentController@store');
    //删除回复
    Route::post('destroy-comment','CommentController@destroy');
    //文章收藏
    Route::post('bookmark-submission','BookmarksController@bookmarkSubmission');
    //删除文章
    Route::post('destroy-submission','SubmissionController@destroy');

    //赞文章
    Route::post('upvote-submission','SubmissionVotesController@upVote');
    //他的专栏
    Route::post('user','HomeController@userArticle');
});
//文章详情
Route::post('article/detail-by-slug','Article\SubmissionController@getBySlug');
//文章回复列表
Route::post('article/comments','Article\CommentController@index');
//点赞
Route::post('support/{source_type}',['uses'=>'SupportController@store'])->where(['source_type'=>'(answer|article|comment)'])->middleware('jwt.auth');
//附近位置
Route::post('location/nearbySearch',['uses'=>'LocationController@nearbySearch'])->middleware('jwt.auth');
//附近的人
Route::post('location/nearbyUser',['uses'=>'LocationController@nearbyUser'])->middleware('jwt.auth');

