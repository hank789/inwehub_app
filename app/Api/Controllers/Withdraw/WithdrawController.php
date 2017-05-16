<?php namespace App\Api\Controllers\Withdraw;
use App\Api\Controllers\Controller;
use App\Events\Frontend\Withdraw\WithdrawCreate;
use App\Exceptions\ApiException;
use App\Logic\WithdrawLogic;
use App\Models\Pay\UserMoney;
use App\Models\Pay\Withdraw;
use App\Models\UserOauth;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午2:11
 * @email: wanghui@yonglibao.com
 */

class WithdrawController extends Controller {
    public function request(Request $request)
    {
        $validateRules = [
            'amount' => 'required|min:1|max:20000',
        ];
        $this->validate($request, $validateRules);
        $amount = $request->input('amount');
        $user = $request->user();
        //是否绑定了微信
        $user_oauth = UserOauth::where('user_id',$user->id)->where('auth_type','weixin')->first();
        if(empty($user_oauth)){
            throw new ApiException(ApiException::WITHDRAW_UNBIND_WEXIN);
        }

        if(RateLimiter::instance()->increase('withdraw',$user->id,60,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $user_money = UserMoney::find($user->id);
        if($amount > $user_money->total_money){
            throw new ApiException(ApiException::WITHDRAW_AMOUNT_INVALID);
        }

        WithdrawLogic::checkUserWithdrawLimit($user->id,$amount);

        event(new WithdrawCreate($user->id,$amount,isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'));
        return self::createJsonData(true,['withdraw_channel'=>Setting()->get('withdraw_channel',Withdraw::WITHDRAW_CHANNEL_WX),'tips'=>'您的提现请求已受理']);

    }
}