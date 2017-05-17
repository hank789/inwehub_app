<?php namespace App\Listeners\Frontend;
/**
 * @author: wanghui
 * @date: 2017/5/16 下午3:23
 * @email: wanghui@yonglibao.com
 */
use App\Events\Frontend\Withdraw\WithdrawCreate;
use App\Events\Frontend\Withdraw\WithdrawProcess;
use App\Exceptions\ApiException;
use App\Logic\MoneyLogLogic;
use App\Logic\WithdrawLogic;
use App\Models\Pay\MoneyLog;
use App\Models\Pay\UserMoney;
use App\Models\Pay\Withdraw;
use App\Models\UserOauth;
use Illuminate\Contracts\Queue\ShouldQueue;


class WithdrawEventListener implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $queue = 'withdraw';

    /**
     * @param WithdrawCreate $event
     */
    public function create($event)
    {
        $user_id = $event->user_id;
        $amount  = $event->amount;
        $user_money = UserMoney::find($user_id);
        if($amount > $user_money->total_money){
            \Log::error('提现金额大于账户余额',['withdraw_amount'=>$amount,'account_money'=>$user_money->total_money]);
            return;
        }
        //是否绑定了微信
        $user_oauth = UserOauth::where('user_id',$user_id)->where('auth_type','weixin')->first();
        if(empty($user_oauth)){
            return;
        }
        try{
            WithdrawLogic::checkUserWithdrawLimit($user_id,$amount);
        } catch (\Exception $e){
            return;
        }

        $withdraw = Withdraw::create([
            'user_id' => $user_id,
            'order_no' => gen_order_number(),
            'amount'  => $amount,
            'withdraw_channel' => Setting()->get('withdraw_channel',Withdraw::WITHDRAW_CHANNEL_WX),
            'client_ip' => $event->client_ip
        ]);
        //减少余额
        $res = MoneyLogLogic::decMoney($user_id,$amount,MoneyLog::MONEY_TYPE_WITHDRAW,$withdraw,0,0);
        if($res == false){
            $withdraw->status = Withdraw::WITHDRAW_STATUS_FAIL;
            $withdraw->response_msg = '扣除余额失败';
            $withdraw->save();
        }else{
            //是否设置了自动提现
            $is_auto = Setting()->get('withdraw_auto',0);
            if($is_auto){
                //变为处理中
                $withdraw->status = Withdraw::WITHDRAW_STATUS_PROCESS;
                $withdraw->save();
                //处理提现
                $this->process(new WithdrawProcess($withdraw->id));
            }
        }

    }


    /**
     * @param WithdrawProcess $event
     */
    public function process($event){
        $withdraw = Withdraw::find($event->withdraw_id);
        $rp = WithdrawLogic::withdrawRequest($withdraw);
        if($rp == false){
            //todo 处理请求失败
        }else{
            MoneyLog::where('source_id',$withdraw->id)->where('source_type',get_class($withdraw))->update([
                'status' => MoneyLog::STATUS_SUCCESS
            ]);
        }
    }


    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            WithdrawCreate::class,
            'App\Listeners\Frontend\WithdrawEventListener@create'
        );
        $events->listen(
            WithdrawProcess::class,
            'App\Listeners\Frontend\WithdrawEventListener@process'
        );

    }

}