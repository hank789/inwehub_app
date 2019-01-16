<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'qq' => [
        'client_id' => env('OAUTH_QQ_KEY'),
        'client_secret' => env('OAUTH_QQ_SECRET'),
        'redirect' => env('OAUTH_QQ_REDIRECT'),
    ],
    'weibo' => [
        'client_id' => env('OAUTH_WEIBO_KEY'),
        'client_secret' => env('OAUTH_WEIBO_SECRET'),
        'redirect' => env('OAUTH_WEIBO_REDIRECT'),
    ],
    'weixin' => [
        'client_id' => env('OAUTH_WEIXIN_KEY'),
        'client_secret' => env('OAUTH_WEIXIN_SECRET'),
        'redirect' => env('OAUTH_WEIXIN_REDIRECT'),
    ],
    'weixinweb' => [
        'client_id' => env('OAUTH_WEIXINWEB_KEY'),
        'client_secret' => env('OAUTH_WEIXINWEB_SECRET'),
        'redirect' => env('OAUTH_WEIXINWEB_REDIRECT'),
    ],
    'boson' => [
        'api_key' => env('BOSON_NLP_KEY')
    ],
    'phantomjs' => [
        'path' => env('PHANTOMJS_PATH')
    ],
    'directmail' => [
        'key' => env('OSS_ACCESS_KEY_ID'),
        'accessSecret'     => env('OSS_ACCESS_KEY_SECRET', 'accessSecret'),
        'address_type' => 1,
        'from_alias' => 'Inwehub',
        'click_trace' => 1,
        'version' => '2015-11-23',
        'region_id' => 'cn-hangzhou',
    ],

];
