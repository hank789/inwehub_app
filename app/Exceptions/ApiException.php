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


    const BAD_REQUEST = 11100;
    const REQUEST_FAIL = 11300;
    const AUTH_FAIL = 11400;
    const INVALID_PARAMS = 11500;

    const SUCCESS = 10000;

    const TOKEN_EXPIRED = 13000;
    const TOKEN_INVALID = 13001;
    const TOKEN_MISSING = 13002;

    const MISSING_PARAMS = 12000;
    const VISIT_LIMIT = 12001;

    const ERROR = 14000;

    const ARGS_ERROR = 14100;
    const PHONE_FORMAT_ERROR = 14101;
    const ARGS_YZM_ERROR = 14102;
    const STRING_LENGTH_ERROR = 14103;
    const LIMIT_ACTION = 14104;
    const YZM_SEND_TOO_MUCH = 14105;
    const OPTION_VERSION_IS_NEW = 14106;

    const USER_REPEAT = 14201;
    const USER_PASSWORD_ERROR = 14202;
    const USER_NEW_PASSWORD_FORMAT_ERROR = 14203;
    const USER_NOT_FOUND = 14204;
    const USER_BLOCKED = 14205;
    const USER_REPORT_DEVICE_ERROR = 14206;
    const USER_LOGIN_NEED_YZM = 14207;
    const USER_LOGIN_LOCK = 14208;
    const USER_ALREADY_CREATE_JX = 14209;
    const USER_ALREADY_BIND_JX = 14210;
    const USER_ALREADY_PWD_JX = 14211;

    const REPAY_MONEY_NOT_FULL = 14401;

    const GET_MEMBER_ACCOUNT_INFO_FALSE = 16001;
    const MONEY_IS_NOT_ENOUGH = 16002;
    const MONEY_IS_NOT_ENOUGH_FOR_FEE = 16003;

    const BANKCARD_ERROR = 17001;

    const WITHDRAW_CUR_DAY_TOTAL_ERROR = 18001;
    const WITHDRAW_SUSPEND = 18002;
    const RECHARGE_MONEY_OVER = 18003;


    public static $errorMessages = [
        self::BAD_REQUEST => '非法的请求',
        self::VISIT_LIMIT => '访问频率过高,请稍后再试',
        self::REQUEST_FAIL => '请求失败',
        self::AUTH_FAIL => '验证失败',
        self::INVALID_PARAMS => '参数错误',
        self::SUCCESS => 'success',
        self::MISSING_PARAMS => '缺少参数',
        self::VISIT_LIMIT => '访问频率过高,请稍后再试',
        self::ERROR => '系统异常',
        self::ARGS_ERROR => '参数错误',
        self::USER_REPEAT => '该手机号已注册',
        self::USER_PASSWORD_ERROR => '用户账号或者密码不正确',
        self::PHONE_FORMAT_ERROR => '手机号格式不正确',
        self::ARGS_YZM_ERROR => '验证码错误',
        self::USER_NEW_PASSWORD_FORMAT_ERROR => '新密码不符合规范',
        self::USER_NOT_FOUND  => '用户不存在',
        self::REPAY_MONEY_NOT_FULL => '请确认您的还款银行卡中有足够的还款额',
        self::STRING_LENGTH_ERROR => '字数过多',
        self::USER_BLOCKED => '用户已锁定',
        self::USER_REPORT_DEVICE_ERROR => '用户设备登记异常',
        self::LIMIT_ACTION => '操作频繁',
        self::YZM_SEND_TOO_MUCH  => '验证码发送次数过多',
        self::OPTION_VERSION_IS_NEW => '已经是最新版本',
        self::GET_MEMBER_ACCOUNT_INFO_FALSE => '获取账户资金失败',
        self::MONEY_IS_NOT_ENOUGH => '余额不足',
        self::MONEY_IS_NOT_ENOUGH_FOR_FEE => '余额不足以支付手续费',
        self::USER_LOGIN_NEED_YZM => '需要验证码',
        self::USER_LOGIN_LOCK => '用户登录锁定',
        self::USER_ALREADY_CREATE_JX => '用户已经开户过',
        self::USER_ALREADY_BIND_JX => '已经绑卡过',
        self::USER_ALREADY_PWD_JX => '已经设置过交易密码',
        self::BANKCARD_ERROR => '银行卡号无效',
        self::WITHDRAW_CUR_DAY_TOTAL_ERROR => '超过单笔最高限额或单天最高限额',
        self::WITHDRAW_SUSPEND => '暂停提现',
        self::RECHARGE_MONEY_OVER => '充值金额超限',
        self::TOKEN_EXPIRED=>'token已过期',
        self::TOKEN_INVALID => 'token无效',
        self::TOKEN_MISSING => 'token缺失'
    ];



}