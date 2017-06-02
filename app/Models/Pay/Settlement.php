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
    protected $fillable = ['id','user_id','source_id','source_type','settlement_date','status'];


    const SETTLEMENT_STATUS_PENDING = 0;
    const SETTLEMENT_STATUS_PROCESS = 1;
    const SETTLEMENT_STATUS_SUCCESS = 2;
    const SETTLEMENT_STATUS_FAIL = 3;
    const SETTLEMENT_STATUS_SUSPEND = 4;


    public static function answerSettlement(Answer $answer){
        $settlement_date = Setting()->get('pay_settlement_cycle',5);

        $object = self::create([
            'user_id' => $answer->user->id,
            'source_id' => $answer->id,
            'source_type' => get_class($answer),
            'settlement_date' => date('Y-m-d',strtotime('+'.$settlement_date.' days')),
            'status' => self::SETTLEMENT_STATUS_PENDING
        ]);
        if ($object){
            $answer->user->userMoney()->increment('settlement_money',$answer->question->price);
        }
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
        }
    }

}