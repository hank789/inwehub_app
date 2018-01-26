<?php namespace App\Models\Pay;
use App\Logic\MoneyLogLogic;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Pay\Settlement
 *
 * @mixin \Eloquent
 * @property int $id
 * @property int $user_id
 * @property int $source_id
 * @property string $source_type
 * @property int $status 结算状态:0待结算,1结算中,2已结算,3结算失败
 * @property string|null $settlement_date 结算日期:Y-m-d
 * @property string|null $actual_amount 实际结算金额
 * @property string|null $actual_fee 实际结算手续费
 * @property string|null $actual_settlement_date 实际结算日期:Y-m-d H:i:s
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereActualAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereActualFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereActualSettlementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereSettlementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereSourceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Pay\Settlement whereUserId($value)
 */
class Settlement extends Model {
    use BelongsToUserTrait;

    protected $table = 'settlement';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','user_id','source_id','source_type','settlement_date','status','actual_amount','actual_fee'];


    const SETTLEMENT_STATUS_PENDING = 0;
    const SETTLEMENT_STATUS_PROCESS = 1;
    const SETTLEMENT_STATUS_SUCCESS = 2;
    const SETTLEMENT_STATUS_FAIL = 3;
    const SETTLEMENT_STATUS_SUSPEND = 4;

    const SOURCE_TYPE_ANSWER = 'App\Models\Answer';
    const SOURCE_TYPE_ORDER  = 'App\Models\Pay\Order';
    const SOURCE_TYPE_REWARD_QUESTION = 'question_reward';
    const SOURCE_TYPE_REWARD_ANSWER = 'answer_reward';
    const SOURCE_TYPE_REWARD_PAY_FOR_VIEW_ANSWER = 'pay_for_view_answer_reward';
    const SOURCE_TYPE_REWARD_COUPON = 'reward_coupon';




    //回答结算
    public static function answerSettlement(Answer $answer){
        if ($answer->question->price <=0) return;

        $settlement_date = Setting()->get('pay_settlement_cycle',5);

        $object = self::create([
            'user_id' => $answer->user->id,
            'source_id' => $answer->id,
            'source_type' => get_class($answer),
            'actual_amount' => $answer->question->price,
            'actual_fee' => MoneyLogLogic::getAnswerFee($answer),
            'settlement_date' => date('Y-m-d',strtotime('+'.$settlement_date.' days')),
            'status' => self::SETTLEMENT_STATUS_PENDING
        ]);
        if ($object){
            $user_money = $answer->user->userMoney;
            $user_money->settlement_money = bcadd($user_money->settlement_money,$answer->question->price,2);
            $user_money->save();
        }
    }


    //问题结算
    public static function questionSettlement(Question $question) {
        //邀请人分红结算，邀请人拿到问题金额5%的分红
        $question_user = $question->user;
        if (empty($question_user->rc_uid) || $question_user->rc_uid <= 0 || $question->price <= 0) {
            return;
        }
        $reward_user = User::find($question_user->rc_uid);
        //每月月底结算
        $settlement_date = date('Y-m-d',strtotime(date('Y-m-1').' +1 month -1 day'));
        $today = date('Y-m-d');
        if ($settlement_date == $today) {
            $settlement_date = date('Y-m-d',strtotime(date('Y-m-1').' +2 month -1 day'));
        }
        //邀请人拿到问题金额5%的分红
        $settlement_money = $question->price * self::getInviteRewardRate();
        $object = self::create([
            'user_id' => $reward_user->id,
            'source_id' => $question->id,
            'source_type' => self::SOURCE_TYPE_REWARD_QUESTION,
            'actual_amount' => $settlement_money,
            'actual_fee' => 0,
            'settlement_date' => $settlement_date,
            'status' => self::SETTLEMENT_STATUS_PENDING
        ]);
        if ($object){
            $reward_user_money = $reward_user->userMoney;
            $reward_user_money->settlement_money = bcadd($reward_user_money->settlement_money,$settlement_money,2);
            $reward_user_money->save();
        }
    }

    //付费围观结算
    public static function payForViewSettlement(Order $order){
        //每月月底结算
        $settlement_date = date('Y-m-d',strtotime(date('Y-m-1').' +1 month -1 day'));
        $today = date('Y-m-d');
        if ($settlement_date == $today) {
            $settlement_date = date('Y-m-d',strtotime(date('Y-m-1').' +2 month -1 day'));
        }
        $answer = $order->answer()->first();
        switch ($answer->question->price) {
            case 1:
                //首问1元，查看收入全部给回答者
                $question_user_per = 0;
                $answer_user_per = 1;
                break;
            case 28:
                //按3：7分成
                $question_user_per = 0.3;
                $answer_user_per = 0.7;
                break;
            case 60:
                //按5：5分成
                $question_user_per = 0.5;
                $answer_user_per = 0.5;
                break;
            case 88:
                //按7：3分成
                $question_user_per = 0.7;
                $answer_user_per = 0.3;
                break;
            case 188:
                //按7：3分成
                $question_user_per = 0.7;
                $answer_user_per = 0.3;
                break;
            default:
                return;
        }
        $answer_user_money = bcmul($order->actual_amount, $answer_user_per,2);
        $question_user_money = bcmul($order->actual_amount, $question_user_per,2);

        if ($answer_user_money > 0) {
            $answer_user_money_model = $answer->user->userMoney;
            $answer_user_money_model->settlement_money = bcadd($answer_user_money_model->settlement_money,$answer_user_money,2);
            $answer_user_money_model->save();
            self::create([
                'user_id' => $answer->user->id,
                'source_id' => $order->id,
                'source_type' => get_class($order),
                'actual_amount' => $answer_user_money,
                'actual_fee'    => self::getPayForViewFee($order, $answer_user_money),
                'settlement_date' => $settlement_date,
                'status' => self::SETTLEMENT_STATUS_PENDING
            ]);
        }
        if ($question_user_money > 0) {
            $question_user_money_model = $answer->question->user->userMoney;
            $question_user_money_model->settlement_money = bcadd($question_user_money_model->settlement_money,$question_user_money,2);
            $question_user_money_model->save();
            self::create([
                'user_id' => $answer->question->user_id,
                'source_id' => $order->id,
                'source_type' => get_class($order),
                'actual_amount' => $question_user_money,
                'actual_fee'    => self::getPayForViewFee($order, $question_user_money),
                'settlement_date' => $settlement_date,
                'status' => self::SETTLEMENT_STATUS_PENDING
            ]);
        }

    }


    public static function getPayForViewFee(Order $order, $amount){
        //目前都是20%的手续费
        $fee_rate = Setting()->get('pay_answer_normal_fee_rate',0.2);
        switch($order->pay_channel){
            case Order::PAY_CHANNEL_IOS_IAP:
                $iap_fee_rate = Setting()->get('pay_answer_iap_fee_rate',0.32);
                $fee_rate += $iap_fee_rate;
                break;
        }
        return bcmul($fee_rate ,$amount,2);

    }


    public static function getInviteRewardRate(){
        return 0.05;
    }

    public function getSettlementMoney(){
        switch($this->source_type){
            case 'App\Models\Answer':
                $answer = Answer::find($this->source_id);
                return $answer->question->price;
                break;
        }
    }

    public function getSettlementFee(){
        switch($this->source_type){
            case 'App\Models\Answer':
                $answer = Answer::find($this->source_id);
                return MoneyLogLogic::getAnswerFee($answer);

                break;
        }
    }

    public function getSettlementName(){
        switch($this->source_type){
            case 'App\Models\Answer':
                return "问答";
                break;
            case 'App\Models\Pay\Order':
                $order = Order::find($this->source_id);
                switch ($order->return_param){
                    case 'view_answer':
                        return '付费围观订单';
                        break;
                }
                break;
            case 'reward_coupon':
                return '红包收入';
                break;
        }
        return $this->source_type;
    }

}