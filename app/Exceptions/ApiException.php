<?php namespace App\Exceptions;

/**
 * @author: wanghui
 * @date: 2017/4/6 下午5:35
 * @email: wanghui@yonglibao.com
 */

use Exception;

/**
 * Class GeneralException.
 */
class ApiException extends Exception
{
    public function __construct($code, \Exception $previous = null)
    {
        parent::__construct(self::$errorMessages[$code], $code, $previous);
    }


    //全局响应码
    const SUCCESS = 1000;
    const TOKEN_EXPIRED=1001;//token过期，需要刷新
    const TOKEN_INVALID=1002;//token无效
    const TOKEN_LOGIN_OTHER=1004;//token在其他设备登陆
    const BAD_REQUEST=1006;//错误的请求
    const REQUEST_FAIL=1007;//请求失败
    const INVALID_PARAMS=1008;//参数错误
    const VISIT_LIMIT=1009;//超过访问频率
    const ERROR=1010;//系统异常
    const MISSING_PARAMS=1011;//参数缺失
    const FILE_NOT_EXIST=1012;//文件不存在


    //用户模块响应码
    const AUTH_FAIL = 1199;
    const USER_PHONE_EXIST = 1101;
    const USER_NOT_FOUND   = 1102;
    const USER_PASSWORD_ERROR = 1103;
    const ARGS_YZM_ERROR = 1104;
    const EXPERT_NEED_CONFIRM = 1105;
    const USER_NEED_CONFIRM = 1106;
    const USER_DATE_RANGE_INVALID = 1107;
    const USER_REGISTRATION_CODE_INVALID = 1108;
    const USER_SUSPEND = 1109;
    const USER_REGISTRATION_CODE_OVERTIME = 1110;



    //问答模块响应码
    const ASK_NEED_USER_INFORMATION = 3000;
    const ASK_ANSWER_PROMISE_TIME_INVALID = 3001;
    const ASK_QUESTION_NOT_EXIST = 3002;
    const ASK_QUESTION_ALREADY_CONFIRMED = 3003;
    const ASK_PAYMENT_EXCEPTION = 3004;
    const ASK_CANNOT_INVITE_SELF = 3005;
    const ASK_INVITE_USER_NOT_FOUND = 3006;
    const ASK_INVITE_USER_MUST_EXPERT = 3007;

    //支付模块响应码
    const PAYMENT_UNKNOWN_CHANNEL = 4004;
    const PAYMENT_UNKNOWN_PAY_TYPE = 4005;
    const WITHDRAW_AMOUNT_INVALID = 4006;
    const WITHDRAW_DAY_COUNT_LIMIT = 4007;
    const WITHDRAW_DAY_AMOUNT_LIMIT = 4008;
    const WITHDRAW_UNBIND_WEXIN = 4009;
    const WITHDRAW_SYSTEM_SUSPEND = 4010;


    public static $errorMessages = [
        //全局响应吗
        self::TOKEN_EXPIRED=>'token已过期',
        self::TOKEN_INVALID => 'token无效',
        self::BAD_REQUEST => '非法的请求',
        self::REQUEST_FAIL => '请求失败',
        self::AUTH_FAIL => '验证失败',
        self::INVALID_PARAMS => '参数错误',
        self::SUCCESS => 'success',
        self::MISSING_PARAMS => '缺少参数',
        self::VISIT_LIMIT => '访问频率过高,请稍后再试',
        self::ERROR => '系统异常',
        self::TOKEN_LOGIN_OTHER => 'token在其他设备登陆',
        self::FILE_NOT_EXIST => '文件不存在',

        //用户模块
        self::USER_PHONE_EXIST => '该手机号已注册',
        self::USER_NOT_FOUND  => '用户不存在',
        self::USER_PASSWORD_ERROR => '用户账号或者密码不正确',
        self::ARGS_YZM_ERROR => '验证码错误',
        self::EXPERT_NEED_CONFIRM => '您的认证申请正在审核中',
        self::USER_NEED_CONFIRM => '您的账户正在审核中,请耐心等待',
        self::USER_DATE_RANGE_INVALID => '起始日期有误',
        self::USER_REGISTRATION_CODE_INVALID => '邀请码错误',
        self::USER_SUSPEND => '您的账户已被禁用',
        self::USER_REGISTRATION_CODE_OVERTIME => '邀请码已过期',


        //问答模块
        self::ASK_NEED_USER_INFORMATION => '稍微花点时间,完成下个人信息,平台为您匹配专家会更精确哦!',
        self::ASK_ANSWER_PROMISE_TIME_INVALID => '格式错误',
        self::ASK_QUESTION_NOT_EXIST => '问题不存在',
        self::ASK_QUESTION_ALREADY_CONFIRMED => '手慢了一步，已经有专家赶在您前面确认应答了，下次加油啊！',
        self::ASK_PAYMENT_EXCEPTION => '支付异常',
        self::ASK_CANNOT_INVITE_SELF => '不能邀请自己',
        self::ASK_INVITE_USER_NOT_FOUND => '邀请者不存在',
        self::ASK_INVITE_USER_MUST_EXPERT => '邀请者必须为专家',

        //支付模块
        self::PAYMENT_UNKNOWN_CHANNEL => '暂不支持该支付渠道',
        self::PAYMENT_UNKNOWN_PAY_TYPE => '未知支付对象',
        self::WITHDRAW_AMOUNT_INVALID => '提现金额有误',
        self::WITHDRAW_DAY_COUNT_LIMIT => '提现单日超次数',
        self::WITHDRAW_DAY_AMOUNT_LIMIT => '提现单日额度超限',
        self::WITHDRAW_UNBIND_WEXIN => '未绑定微信',
        self::WITHDRAW_SYSTEM_SUSPEND => '系统暂停提现'
    ];



}