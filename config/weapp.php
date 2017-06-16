<?php
/**
 * @author: wanghui
 * @date: 2017/6/16 下午1:51
 * @email: wanghui@yonglibao.com
 */

return [
    /**
     * 小程序APPID
     */
    'appid' => env('WEAPP_APP_ID','your AppID'),
    /**
     * 小程序Secret
     */
    'secret' => env('WEAPP_SECRET','your AppSecret'),
    /**
     * 小程序登录凭证 code 获取 session_key 和 openid 地址，不需要改动
     */
    'code2session_url' => "https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
];