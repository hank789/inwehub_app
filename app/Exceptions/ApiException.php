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
    const USER_RESUME_UPLOAD_LIMIT = 1111;
    const USER_SUBMIT_PROJECT_NEED_COMPANY = 1112;
    const USER_OAUTH_BIND_OTHERS = 1113;
    const USER_WEIXIN_UNOAUTH = 1114;
    const USER_WEIXIN_NEED_REGISTER = 1115;
    const USER_WEIXIN_REGISTER_NEED_CODE = 1116;
    const USER_REGISTRATION_CODE_USED = 1117;
    const USER_CANNOT_FOLLOWED_SELF = 1118;
    const USER_COMPANY_APPLY_REPEAT = 1119;
    const USER_REGISTRATION_CODE_EXPIRED = 1120;

    //问答模块响应码
    const ASK_NEED_USER_INFORMATION = 3000;
    const ASK_ANSWER_PROMISE_TIME_INVALID = 3001;
    const ASK_QUESTION_NOT_EXIST = 3002;
    const ASK_QUESTION_ALREADY_CONFIRMED = 3003;
    const ASK_PAYMENT_EXCEPTION = 3004;
    const ASK_CANNOT_INVITE_SELF = 3005;
    const ASK_INVITE_USER_NOT_FOUND = 3006;
    const ASK_INVITE_USER_MUST_EXPERT = 3007;
    const ASK_QUESTION_ALREADY_ANSWERED = 3008;
    const ASK_QUESTION_ALREADY_REJECTED = 3009;
    const ASK_QUESTION_ALREADY_SELF_CONFIRMED = 3010;
    const ASK_ANSWER_CONTENT_TOO_SHORT = 3011;
    const ASK_ANSWER_NOT_EXIST = 3012;
    const ASK_ANSWER_FEEDBACK_EXIST = 3013;
    const ASK_FEEDBACK_SELF_ANSWER = 3014;

    //支付模块响应码
    const PAYMENT_UNKNOWN_CHANNEL = 4004;
    const PAYMENT_UNKNOWN_PAY_TYPE = 4005;
    const WITHDRAW_AMOUNT_INVALID = 4006;
    const WITHDRAW_DAY_COUNT_LIMIT = 4007;
    const WITHDRAW_DAY_AMOUNT_LIMIT = 4008;
    const WITHDRAW_UNBIND_WEXIN = 4009;
    const WITHDRAW_SYSTEM_SUSPEND = 4010;

    //企业模块响应码
    const PROJECT_NOT_FIND = 5000;

    //活动模块
    const ACTIVITY_TIME_OVER = 6000;
    const ACTIVITY_PERMISSION_LIMIT = 6001;
    const ACTIVITY_DAILY_SIGN_REPEAT = 6002;

    //文章模块
    const ARTICLE_URL_ALREADY_EXIST = 6101;
    const ARTICLE_GET_URL_TITLE_ERROR = 6102;
    const ARTICLE_CATEGORY_NOT_EXIST = 6103;
    const ARTICLE_NOT_EXIST = 6104;


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
        self::USER_RESUME_UPLOAD_LIMIT => '请明天再来上传简历信息',
        self::USER_SUBMIT_PROJECT_NEED_COMPANY => '您的账户类型，暂无法使用此功能，如需申请企业账户请发送基本信息到hi@inwehub.com',
        self::USER_OAUTH_BIND_OTHERS => '该微信号已经绑定过其他InweHub账号，请更换其他微信账号绑定。如有疑惑请联系客服小哈hi@inwehub.com',
        self::USER_WEIXIN_UNOAUTH    => '微信未授权',
        self::USER_WEIXIN_NEED_REGISTER => '需要注册',
        self::USER_WEIXIN_REGISTER_NEED_CODE => '新注册用户需要填写邀请码',
        self::USER_REGISTRATION_CODE_USED => '此邀请码已被使用，谢谢您的支持！',
        self::USER_CANNOT_FOLLOWED_SELF => '您不能关注自己',
        self::USER_COMPANY_APPLY_REPEAT => '企业申请已经提交,请耐心等待',
        self::USER_REGISTRATION_CODE_EXPIRED => '您的邀请码已经过期，请重新获取有效邀请码',


        //问答模块
        self::ASK_NEED_USER_INFORMATION => '稍微花点时间补充下个人信息，平台为您匹配专家才会更精准额！个人信息完整度90%以上才能解锁问答等功能。',
        self::ASK_ANSWER_PROMISE_TIME_INVALID => '格式错误',
        self::ASK_QUESTION_NOT_EXIST => '问题不存在',
        self::ASK_QUESTION_ALREADY_CONFIRMED => '手慢了一步，已经有专家赶在您前面确认应答了，下次加油啊！',
        self::ASK_PAYMENT_EXCEPTION => '支付异常',
        self::ASK_CANNOT_INVITE_SELF => '不能向自己提问',
        self::ASK_INVITE_USER_NOT_FOUND => '邀请者不存在',
        self::ASK_INVITE_USER_MUST_EXPERT => '邀请者必须为专家',
        self::ASK_QUESTION_ALREADY_ANSWERED => '您已回答过此问题',
        self::ASK_QUESTION_ALREADY_REJECTED => '您已拒绝回答该问题',
        self::ASK_QUESTION_ALREADY_SELF_CONFIRMED => '您已经确认过此问题',
        self::ASK_ANSWER_CONTENT_TOO_SHORT => '您的回答内容太少了,请完善内容',
        self::ASK_ANSWER_NOT_EXIST => '回答不存在',
        self::ASK_ANSWER_FEEDBACK_EXIST => '您已评价过该回答',
        self::ASK_FEEDBACK_SELF_ANSWER => '您不能评价自己的回答',

        //支付模块
        self::PAYMENT_UNKNOWN_CHANNEL => '暂不支持该支付渠道',
        self::PAYMENT_UNKNOWN_PAY_TYPE => '未知支付对象',
        self::WITHDRAW_AMOUNT_INVALID => '提现金额有误',
        self::WITHDRAW_DAY_COUNT_LIMIT => '提现单日超次数',
        self::WITHDRAW_DAY_AMOUNT_LIMIT => '提现单日额度超限',
        self::WITHDRAW_UNBIND_WEXIN => '未绑定微信',
        self::WITHDRAW_SYSTEM_SUSPEND => '系统暂停提现',


        //企业模块
        self::PROJECT_NOT_FIND => '需求不存在',

        //活动模块
        self::ACTIVITY_TIME_OVER => '活动已结束',
        self::ACTIVITY_PERMISSION_LIMIT => '权限不够',
        self::ACTIVITY_DAILY_SIGN_REPEAT => '重复签到',

        //文章feed
        self::ARTICLE_URL_ALREADY_EXIST => '您提交的网址已经存在',
        self::ARTICLE_GET_URL_TITLE_ERROR => '获取文章标题失败，请手动输入',
        self::ARTICLE_CATEGORY_NOT_EXIST => '频道不存在',
        self::ARTICLE_NOT_EXIST => '文章不存在'
    ];



}