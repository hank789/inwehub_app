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
    protected $fillable = ['user_id', 'action','coins','credits','source_id','source_subject','current_coins','current_credits','created_at'];
    public $timestamps = false;

    //注册与日常登陆
    const KEY_REGISTER = 'register';
    const KEY_INVITE_USER = 'invite_user';
    const KEY_FIRST_USER_SIGN_DAILY = 'user_sign_daily';

    //信息维护与认证
    const KEY_UPLOAD_AVATAR = 'upload_avatar';
    const KEY_USER_INFO_COMPLETE = 'user_info_complete';
    const KEY_EXPERT_VALID = 'expert_valid';
    const KEY_COMPANY_VALID = 'company_valid';

    //动态与分享
    const KEY_READHUB_NEW_SUBMISSION = 'readhub_new_submission';
    const KEY_READHUB_SUBMISSION_UPVOTE = 'readhub_submission_upvote';
    const KEY_READHUB_SUBMISSION_COLLECT = 'readhub_submission_collect';
    const KEY_READHUB_SUBMISSION_COMMENT = 'readhub_submission_comment';
    const KEY_READHUB_SUBMISSION_SHARE = 'readhub_submission_share';

    //专业问答
    const KEY_FIRST_ASK = 'first_ask';
    const KEY_ASK = 'ask';
    const KEY_PAY_FOR_VIEW_ANSWER = 'pay_for_view_answer';
    const KEY_FIRST_ANSWER = 'first_answer';
    const KEY_ANSWER = 'answer';
    const KEY_ANSWER_UPVOTE = 'answer_upvote';
    const KEY_ANSWER_COMMENT = 'answer_comment';
    const KEY_ANSWER_SHARE = 'answer_share';
    const KEY_RATE_ANSWER_GOOD = 'rate_answer_good';
    const KEY_RATE_ANSWER_BAD = 'rate_answer_bad';

    //互动问答
    const KEY_FIRST_COMMUNITY_ASK = 'first_community_ask';
    const KEY_COMMUNITY_ASK = 'community_ask';
    const KEY_FIRST_COMMUNITY_ANSWER = 'first_community_answer';
    const KEY_COMMUNITY_ANSWER = 'community_answer';
    const KEY_COMMUNITY_ASK_ANSWERED = 'community_ask_answered';
    const KEY_COMMUNITY_ASK_FOLLOWED = 'community_ask_followed';
    const KEY_COMMUNITY_ANSWER_UPVOTE = 'community_answer_upvote';
    const KEY_COMMUNITY_ANSWER_COLLECT = 'community_answer_collect';
    const KEY_COMMUNITY_ANSWER_COMMENT = 'community_answer_comment';
    const KEY_COMMUNITY_ANSWER_SHARE = 'community_answer_share';
    const KEY_COMMUNITY_ANSWER_INVITED = 'community_answer_invited';

    //项目与机遇
    const KEY_PUBLISH_PRO_OPPORTUNITY = 'publish_pro_opportunity';
    const KEY_SIGN_UP_PRO_OPPORTUNITY = 'sign_up_pro_opportunity';
    const KEY_PRO_OPPORTUNITY_SIGNED = 'pro_opportunity_signed';
    const KEY_PRO_OPPORTUNITY_SHARED = 'pro_opportunity_shared';
    const KEY_PRO_OPPORTUNITY_UPVOTED = 'pro_opportunity_upvoted';
    const KEY_PRO_OPPORTUNITY_COMMENTED = 'pro_opportunity_commented';
    const KEY_PRO_OPPORTUNITY_COLLECTED = 'pro_opportunity_collected';
    const KEY_PRO_OPPORTUNITY_RECOMMEND = 'pro_opportunity_recommend';
    const KEY_RECOMMEND_PRO_OPPORTUNITY = 'recommend_pro_opportunity';



    const KEY_LOGIN = 'login';

    const KEY_ASK_GOOD = 'ask_good';
    const KEY_ANSWER_GOOD = 'answer_good';
    const KEY_SHARE_SUCCESS = 'share_success';
    const KEY_REWARD_USER = 'reward_user';
    const KEY_PUNISH_USER = 'punish_user';
    const KEY_NEW_COMMENT = 'new_comment';
    const KEY_NEW_UPVOTE = 'new_upvote';
    const KEY_NEW_COLLECT = 'new_collect';
    const KEY_NEW_FOLLOW = 'new_follow';
    const KEY_NEW_ANSWER_FEEDBACK = 'new_answer_feedback';


    public static $creditSetting = [
        //注册与日常登陆
        self::KEY_REGISTER => ['backend_label'=>'注册','notice_user'=>'注册成功'],
        self::KEY_INVITE_USER => ['backend_label'=>'每邀请一位好友并激活(邀请者加分)','notice_user'=>'邀请好友'],
        self::KEY_FIRST_USER_SIGN_DAILY => ['backend_label'=>'每日签到','notice_user'=>'每日签到'],
        //信息维护与认证
        self::KEY_UPLOAD_AVATAR => ['backend_label'=>'上传头像','notice_user'=>'上传头像成功'],
        self::KEY_USER_INFO_COMPLETE => ['backend_label'=>'简历完成','notice_user'=>'简历完成'],
        self::KEY_EXPERT_VALID => ['backend_label'=>'完成专家认证','notice_user'=>'完成专家认证'],
        self::KEY_COMPANY_VALID => ['backend_label'=>'企业认证成功','notice_user'=>'企业认证成功'],
        //动态与分享
        self::KEY_READHUB_NEW_SUBMISSION => ['backend_label'=>'动态与分享','notice_user'=>'发布成功'],
        self::KEY_READHUB_SUBMISSION_UPVOTE => ['backend_label'=>'动态分享被点赞(发布者加分)','notice_user'=>'点赞成功'],
        self::KEY_READHUB_SUBMISSION_COMMENT => ['backend_label'=>'动态分享被回复(发布者加分)','notice_user'=>'回复成功'],
        self::KEY_READHUB_SUBMISSION_COLLECT => ['backend_label'=>'动态分享被收藏(发布者加分)','notice_user'=>'收藏成功'],
        self::KEY_READHUB_SUBMISSION_SHARE => ['backend_label'=>'动态分享被转发(发布者加分)','notice_user'=>'转发成功'],
        //专业问答
        self::KEY_FIRST_ASK => ['backend_label'=>'完成首次专业提问','notice_user'=>'完成首次提问'],
        self::KEY_ASK => ['backend_label'=>'专业提问','notice_user'=>'提问成功'],
        self::KEY_PAY_FOR_VIEW_ANSWER => ['backend_label'=>'付费围观答案(提问者加分)','notice_user'=>'付费围观成功'],
        self::KEY_FIRST_ANSWER => ['backend_label'=>'完成首次专业回答','notice_user'=>'完成首次回答'],
        self::KEY_ANSWER => ['backend_label'=>'回答专业问答','notice_user'=>'回答成功'],
        self::KEY_ANSWER_UPVOTE => ['backend_label'=>'专业回答被点赞(回答者加分)','notice_user'=>'点赞成功'],
        self::KEY_ANSWER_COMMENT => ['backend_label'=>'专业回答被回复(回答者加分)','notice_user'=>'回复成功'],
        self::KEY_ANSWER_SHARE => ['backend_label'=>'专业回答被转发(回答者加分)','notice_user'=>'转发成功'],
        self::KEY_RATE_ANSWER_GOOD => ['backend_label'=>'专业回答好评(4星以上)','notice_user'=>'评分成功'],
        self::KEY_RATE_ANSWER_BAD => ['backend_label'=>'专业回答差评(2星以上)','notice_user'=>'评分成功'],
        //互动问答
        self::KEY_FIRST_COMMUNITY_ASK => ['backend_label'=>'完成首次互动提问','notice_user'=>'完成首次提问'],
        self::KEY_COMMUNITY_ASK => ['backend_label'=>'互动提问','notice_user'=>'提问成功'],
        self::KEY_COMMUNITY_ASK_ANSWERED => ['backend_label'=>'互动问答被回答（提问者加分）','notice_user'=>'回答成功'],
        self::KEY_COMMUNITY_ASK_FOLLOWED => ['backend_label'=>'互动问答被关注（提问者加分）','notice_user'=>'关注成功'],
        self::KEY_FIRST_COMMUNITY_ANSWER => ['backend_label'=>'完成首次互助回答(回答者加分)','notice_user'=>'完成首次回答'],
        self::KEY_COMMUNITY_ANSWER => ['backend_label'=>'回答互助问答(回答者加分)','notice_user'=>'回答成功'],
        self::KEY_COMMUNITY_ANSWER_UPVOTE => ['backend_label'=>'互动回答被点赞（回答者加分）','notice_user'=>'点赞成功'],
        self::KEY_COMMUNITY_ANSWER_COLLECT => ['backend_label'=>'互动回答被收藏（回答者加分）','notice_user'=>'收藏成功'],
        self::KEY_COMMUNITY_ANSWER_COMMENT => ['backend_label'=>'互动回答被回复（回答者加分）','notice_user'=>'回复成功'],
        self::KEY_COMMUNITY_ANSWER_SHARE => ['backend_label'=>'互动回答被转发（回答者加分）','notice_user'=>'分享成功'],
        self::KEY_COMMUNITY_ANSWER_INVITED => ['backend_label'=>'邀请互动回答（邀请者加分）','notice_user'=>'邀请成功'],
        //项目与机遇
        self::KEY_PUBLISH_PRO_OPPORTUNITY => ['backend_label'=>'发布项目与机遇','notice_user'=>'发布成功'],
        self::KEY_SIGN_UP_PRO_OPPORTUNITY => ['backend_label'=>'报名项目与机遇(报名者加分)','notice_user'=>'报名成功'],
        self::KEY_PRO_OPPORTUNITY_SIGNED  => ['backend_label'=>'项目与机遇被报名(发布者加分)','notice_user'=>'报名成功'],
        self::KEY_PRO_OPPORTUNITY_RECOMMEND  => ['backend_label'=>'项目与机遇被推荐(发布者加分)','notice_user'=>'推荐成功'],
        self::KEY_PRO_OPPORTUNITY_UPVOTED => ['backend_label'=>'项目与机遇被点赞(发布者加分)','notice_user'=>'点赞成功'],
        self::KEY_PRO_OPPORTUNITY_COMMENTED => ['backend_label'=>'项目与机遇被评论(发布者加分)','notice_user'=>'评论成功'],
        self::KEY_PRO_OPPORTUNITY_COLLECTED => ['backend_label'=>'项目与机遇被收藏(发布者加分)','notice_user'=>'收藏成功'],
        self::KEY_PRO_OPPORTUNITY_SHARED => ['backend_label'=>'项目与机遇被转发(发布者加分)','notice_user'=>'转发成功'],
        self::KEY_RECOMMEND_PRO_OPPORTUNITY => ['backend_label'=>'推荐项目与机遇(推荐者加分)','notice_user'=>'推荐成功'],


        self::KEY_LOGIN => ['backend_label'=>'每日登陆','notice_user'=>'每日登陆'],
        self::KEY_ASK_GOOD => ['backend_label'=>'优质提问','notice_user'=>'优质提问'],
        self::KEY_ANSWER_GOOD => ['backend_label'=>'优质回答','notice_user'=>'优质回答'],
        self::KEY_SHARE_SUCCESS => ['backend_label'=>'分享成功','notice_user'=>'分享成功'],
        self::KEY_REWARD_USER => ['backend_label'=>'奖励','notice_user'=>'奖励'],
        self::KEY_PUNISH_USER => ['backend_label'=>'惩罚','notice_user'=>'惩罚'],
        self::KEY_NEW_COMMENT => ['backend_label'=>'回复成功','notice_user'=>'回复成功'],
        self::KEY_NEW_UPVOTE => ['backend_label'=>'点赞成功','notice_user'=>'点赞成功'],
        self::KEY_NEW_COLLECT => ['backend_label'=>'收藏成功','notice_user'=>'收藏成功'],
        self::KEY_NEW_FOLLOW => ['backend_label'=>'关注成功','notice_user'=>'关注成功'],
        self::KEY_NEW_ANSWER_FEEDBACK => ['backend_label'=>'专业问答评分成功','notice_user'=>'评价成功'],

    ];
}
