<?php
/**
 * @author: wanghui
 * @date: 2017/5/15 下午3:18
 * @email: wanghui@yonglibao.com
 */

return [
    //微信app支付
    'wechat' => [
        'use_sandbox'       => env('PAYMENT_WECHAT_SANDBOX',false),// 是否使用 微信支付仿真测试系统

        'app_id'            => env('PAYMENT_WECHAT_APP_ID'),  // 公众账号ID
        'mch_id'            => env('PAYMENT_WECHAT_MCH_ID'),// 商户id
        'md5_key'           => env('PAYMENT_WECHAT_MD5_KEY'),// md5 秘钥
        'app_cert_pem'      => storage_path('payment/wx/pem') . DIRECTORY_SEPARATOR . 'apiclient_cert.pem',
        'app_key_pem'       => storage_path('payment/wx/pem') . DIRECTORY_SEPARATOR . 'apiclient_key.pem',
        'sign_type'         => 'MD5',// MD5  HMAC-SHA256
        'limit_pay'         => [
            'no_credit',
        ],// 指定不能使用信用卡支付   不传入，则均可使用
        'fee_type'          => 'CNY',// 货币类型  当前仅支持该字段

        'notify_url'        => env('PAYMENT_WECHAT_NOTIFY_URL'),//支付结果异步通知

        'redirect_url'      => env('PAYMENT_WECHAT_REDIRECT_URL'),// 如果是h5支付，可以设置该值，返回到指定页面

        'return_raw'        => false,// 在处理回调时，是否直接返回原始数据，默认为true
    ],
    //微信公众号支付
    'wechat_pub' => [
        'use_sandbox'       => env('PAYMENT_WECHAT_SANDBOX',false),// 是否使用 微信支付仿真测试系统

        'app_id'            => env('PAYMENT_WECHAT_PUB_APP_ID'),  // 公众账号ID
        'mch_id'            => env('PAYMENT_WECHAT_PUB_MCH_ID'),// 商户id
        'md5_key'           => env('PAYMENT_WECHAT_PUB_MD5_KEY'),// md5 秘钥
        'app_cert_pem'      => storage_path('payment/wx_pub/pem') . DIRECTORY_SEPARATOR . 'apiclient_cert.pem',
        'app_key_pem'       => storage_path('payment/wx_pub/pem') . DIRECTORY_SEPARATOR . 'apiclient_key.pem',
        'sign_type'         => 'MD5',// MD5  HMAC-SHA256
        'limit_pay'         => [
            'no_credit',
        ],// 指定不能使用信用卡支付   不传入，则均可使用
        'fee_type'          => 'CNY',// 货币类型  当前仅支持该字段

        'notify_url'        => env('PAYMENT_WECHAT_PUB_NOTIFY_URL'),//支付结果异步通知

        'redirect_url'      => env('PAYMENT_WECHAT_PUB_REDIRECT_URL'),// 如果是h5支付，可以设置该值，返回到指定页面

        'return_raw'        => false,// 在处理回调时，是否直接返回原始数据，默认为true
    ],
    //微信小程序支付
    'wechat_lite' => [
        'use_sandbox'       => env('PAYMENT_WECHAT_SANDBOX',false),// 是否使用 微信支付仿真测试系统

        'app_id'            => env('WEAPP_APP_ID'),  // 公众账号ID
        'mch_id'            => env('PAYMENT_WECHAT_PUB_MCH_ID'),// 商户id
        'md5_key'           => env('PAYMENT_WECHAT_PUB_MD5_KEY'),// md5 秘钥
        'app_cert_pem'      => storage_path('payment/wx_pub/pem') . DIRECTORY_SEPARATOR . 'apiclient_cert.pem',
        'app_key_pem'       => storage_path('payment/wx_pub/pem') . DIRECTORY_SEPARATOR . 'apiclient_key.pem',
        'sign_type'         => 'MD5',// MD5  HMAC-SHA256
        'limit_pay'         => [
            'no_credit',
        ],// 指定不能使用信用卡支付   不传入，则均可使用
        'fee_type'          => 'CNY',// 货币类型  当前仅支持该字段

        'notify_url'        => env('PAYMENT_WECHAT_LITE_NOTIFY_URL'),//支付结果异步通知

        'redirect_url'      => env('PAYMENT_WECHAT_PUB_REDIRECT_URL'),// 如果是h5支付，可以设置该值，返回到指定页面

        'return_raw'        => false,// 在处理回调时，是否直接返回原始数据，默认为true
    ],
    'iap' => [
        'ids' => [1=>'qa1',28=>'qa28',60=>'qa60', 88=>'qa88',188=>'qa188','qa_see1'=>'qa_see1'],//请求商品的标识,key表示金额
    ]
];