<?php

return [
    'version' => 'Interv0.1',
    'release' => '20181007',
    'user_cache_time' => 1, //用户数据缓存时间单位分钟
    'user_invite_limit' => 10000000,//用户邀请回答限制数
    'admin' => [
        'page_size' => 15,  //后台分页列表显示数目
    ],
    'api_data_page_size' => 20,//api数据每页返回多少条目
    'user_actions' => [   //积分操作动作
        'login'    => '登录',
        'register' => '注册',
        'ask'      => '发起提问',
        'first_ask'=> '首次提问',
        'answer'   => '回答问题',
        'first_answer' => '首次回答问题',
        'follow_question' => '关注了问题',
        'append_reward' => '对问题追加悬赏',
        'answer_adopted' => '回答被采纳',
        'create_article' => '发表了文章',
        'exchange' => '兑换商品',
        'charge' => '金币充值',
        'reward_user' => '系统奖励',
        'punish_user' => '金币充值',
        'expert_valid' => '专家认证',
        'invite_user'  => '邀请好友',
        'ask_good'     => '优质提问',
        'answer_good'  => '优质回答',
        'user_sign_daily' => '每日签到',
        'user_info_complete' => '简历完成',
        'upload_avatar'      => '头像上传'
    ],
    'notification_types' =>[
        'answer'  => '回答了问题',
        'reply_comment'  => '回复了',
        'comment_question' => '评论了问题',
        'comment_answer' => '评论了回答',
        'comment_article' => '评论了文章',
        'follow_question' => '关注了问题',
        'invite_answer' => '邀请您回答问题',
        'adopt_answer' => '采纳了您的回答',
        'create_article' => '发表了文章',
        'follow_user' => '关注了你',
    ],
    'summernote'=>[
        'ask' => "['common', ['style','bold','ol','link','picture','clear','fullscreen']]",
        'blog' => "['common', ['style','bold','color','ol', 'paragraph','table','link','picture','clear','fullscreen']]",
    ],

    'upload' =>[
        'image'=>[
            'max_size' => '2048' //图片上传大小 单位是kb
        ]
    ],
    'mail_drivers' => [
        'smtp' => '连接 SMTP 服务器发送',
        'sendmail' => '通过sendmail方式进行发送',
    ],
    'category_types' => [
        'questions' => '问题',
        'articles' => '文章',
        'enterprise_review' => '点评'
    ],
    'user_info_valid_percent' => '90',//用户信息完整度要求百分比


];