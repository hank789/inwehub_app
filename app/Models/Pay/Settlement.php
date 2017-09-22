<?php namespace App\Models\Pay;
use App\Logic\MoneyLogLogic;
use App\Models\Answer;
use App\Models\Relations\BelongsToUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
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


    //回答结算
    public static function answerSettlement(Answer $answer){
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
            $answer->user->userMoney()->increment('settlement_money',$answer->question->price);
        }
    }

    //付费围观结算
    public static function payForViewSettlement(Order $order){
        //每月月底结算
        $settlement_date = date('Y-m-d',strtotime(date('Y-m-1').' +1 month -1 day'));

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
        }
        $answer_user_money = bcmul($order->actual_amount, $answer_user_per,2);
        $question_user_money = bcmul($order->actual_amount, $question_user_per,2);

        if ($answer_user_money > 0) {
            $answer->user->userMoney()->increment('settlement_money',$answer_user_money);
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
            $answer->question->user->userMoney()->increment('settlement_money',$question_user_money);
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
        $fee_rate = 0;
        switch($order->pay_channel){
            case Order::PAY_CHANNEL_IOS_IAP:
                $iap_fee_rate = Setting()->get('pay_answer_iap_fee_rate',0.32);
                $fee_rate += $iap_fee_rate;
                break;
        }
        return bcmul($fee_rate ,$amount,2);

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
        }
    }

}