<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Relations\BelongsToUserTrait;

/**
 * App\Models\Credit
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property int $source_id
 * @property string $source_subject
 * @property int $coins
 * @property int $credits
 * @property string $created_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereAction($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereCoins($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereCredits($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereSourceSubject($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Credit whereUserId($value)
 * @mixin \Eloquent
 * @property int $current_credits
 * @property int $current_coins
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Credit whereCurrentCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Credit whereCurrentCredits($value)
 */
class Credit extends Model
{
    use BelongsToUserTrait;
    protected $table = 'credits';
    protected $fillable = ['user_id', 'action','coins','credits','source_id','source_subject','created_at'];
    public $timestamps = false;

    const KEY_REGISTER = 'register';
    const KEY_UPLOAD_AVATAR = 'upload_avatar';
    const KEY_USER_INFO_COMPLETE = 'user_info_complete';
    const KEY_FIRST_USER_SIGN_DAILY = 'user_sign_daily';
    const KEY_LOGIN = 'login';
    const KEY_FIRST_ASK = 'first_ask';
    const KEY_FIRST_COMMUNITY_ASK = 'first_community_ask';
    const KEY_ASK = 'ask';
    const KEY_COMMUNITY_ASK = 'community_ask';
    const KEY_FIRST_ANSWER = 'first_answer';
    const KEY_FIRST_COMMUNITY_ANSWER = 'first_community_answer';
    const KEY_ANSWER = 'answer';
    const KEY_COMMUNITY_ANSWER = 'community_answer';
    const KEY_ANSWER_OVER_PROMISE_TIME_HOURLY = 'answer_over_promise_time_hourly';
    const KEY_OVER_PROMISE_TIME_MAX = 'answer_over_promise_time_max';
    const KEY_ASK_GOOD = 'ask_good';
    const KEY_ANSWER_GOOD = 'answer_good';
    const KEY_INVITE_USER = 'invite_user';
    const KEY_EXPERT_VALID = 'expert_valid';
    const KEY_READHUB_NEW_COMMENT = 'readhub_new_comment';
    const KEY_READHUB_NEW_SUBMISSION = 'readhub_new_submission';
    const KEY_SHARE_SUCCESS = 'share_success';
    const KEY_REWARD_USER = 'reward_user';
    const KEY_PUNISH_USER = 'punish_user';
    const KEY_RATE_ANSWER = 'rate_answer';
    const KEY_FEEDBACK_RATE_ANSWER = 'feedback_rate_answer';


    public static $creditSetting = [
        self::KEY_REGISTER => ['backend_label'=>'注册','notice_user'=>'注册成功'],
        self::KEY_UPLOAD_AVATAR => ['backend_label'=>'上传头像','notice_user'=>'上传头像成功'],
        self::KEY_USER_INFO_COMPLETE => ['backend_label'=>'简历完成','notice_user'=>'简历完成'],
        self::KEY_FIRST_USER_SIGN_DAILY => ['backend_label'=>'每日签到','notice_user'=>'每日签到'],
        self::KEY_LOGIN => ['backend_label'=>'每日登陆','notice_user'=>'每日登陆'],
        self::KEY_FIRST_ASK => ['backend_label'=>'完成首次专业提问','notice_user'=>'完成首次提问'],
        self::KEY_FIRST_COMMUNITY_ASK => ['backend_label'=>'完成首次互助提问','notice_user'=>'完成首次提问'],
        self::KEY_ASK => ['backend_label'=>'专业提问','notice_user'=>'提问成功'],
        self::KEY_COMMUNITY_ASK => ['backend_label'=>'互助提问','notice_user'=>'提问成功'],
        self::KEY_FIRST_ANSWER => ['backend_label'=>'完成首次专业回答','notice_user'=>'完成首次回答'],
        self::KEY_FIRST_COMMUNITY_ANSWER => ['backend_label'=>'完成首次互助回答','notice_user'=>'完成首次回答'],
        self::KEY_ANSWER => ['backend_label'=>'专业问答回答','notice_user'=>'回答成功'],
        self::KEY_COMMUNITY_ANSWER => ['backend_label'=>'互助问答回答','notice_user'=>'回答成功'],
        self::KEY_ANSWER_OVER_PROMISE_TIME_HOURLY => ['backend_label'=>'超出承诺时间未回答每小时(扣分)','notice_user'=>'超时未回答'],
        self::KEY_OVER_PROMISE_TIME_MAX => ['backend_label'=>'超出承诺时间未回答最多扣','notice_user'=>'超出承诺时间未回答最多扣'],
        self::KEY_ASK_GOOD => ['backend_label'=>'优质提问','notice_user'=>'优质提问'],
        self::KEY_ANSWER_GOOD => ['backend_label'=>'优质回答','notice_user'=>'优质回答'],
        self::KEY_INVITE_USER => ['backend_label'=>'每邀请一位好友并激活','notice_user'=>'邀请好友'],
        self::KEY_EXPERT_VALID => ['backend_label'=>'完成专家认证','notice_user'=>'完成专家认证'],
        self::KEY_READHUB_NEW_COMMENT => ['backend_label'=>'阅读回复','notice_user'=>'回复成功'],
        self::KEY_READHUB_NEW_SUBMISSION => ['backend_label'=>'阅读发文','notice_user'=>'发布成功'],
        self::KEY_SHARE_SUCCESS => ['backend_label'=>'分享成功','notice_user'=>'分享成功'],
        self::KEY_REWARD_USER => ['backend_label'=>'奖励','notice_user'=>'奖励'],
        self::KEY_PUNISH_USER => ['backend_label'=>'惩罚','notice_user'=>'惩罚'],
        self::KEY_RATE_ANSWER => ['backend_label'=>'专业回答评价','notice_user'=>'评分成功'],
        self::KEY_FEEDBACK_RATE_ANSWER => ['backend_label'=>'专业回答围观者评价','notice_user'=>'评分成功']

    ];
}
