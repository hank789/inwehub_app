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
use Illuminate\Support\Facades\Auth;

/**
 * @author: wanghui
 * @date: 2017/5/16 下午2:11
 * @email: hank.huiwang@gmail.com
 */

class WithdrawController extends Controller {
    public function request(Request $request)
    {
        $min = Setting()->get('withdraw_per_min_money',10);
        $max = Setting()->get('withdraw_per_max_money',2000);

        $validateRules = [
            'amount' => 'required|numeric',
            'password' => 'required'
        ];
        $this->validate($request, $validateRules);
        $amount = $request->input('amount');
        if ($amount < $min || $amount > $max) {
            throw new ApiException(ApiException::WITHDRAW_AMOUNT_INVALID);
        }
        $user = $request->user();
        $limit = RateLimiter::instance()->getValue('withdraw_password_error_'.date('Ymd'),$user->id);
        if ($limit >= 3) {
            throw new ApiException(ApiException::WITHDRAW_PASSWORD_LIMIT);
        }

        if (!Auth::validate(['mobile'=>$user->mobile,'password'=>$request->input('password')])) {
            $limit = RateLimiter::instance()->increaseBy('withdraw_password_error_'.date('Ymd'),$user->id,1,86400);
            return self::createJsonData(false,[],ApiException::WITHDRAW_PASSWORD_ERROR,'密码输入错误，今天您还可以输入'.(3-$limit).'次');
        }

        //是否绑定了微信
        $user_oauth = UserOauth::where('user_id',$user->id)->whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH])->where('status',1)->orderBy('updated_at','desc')->first();
        if(empty($user_oauth)){
            throw new ApiException(ApiException::WITHDRAW_UNBIND_WEXIN);
        }

        if(RateLimiter::instance()->increase('withdraw',$user->id,6,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $user_money = UserMoney::find($user->id);
        if($amount > $user_money->total_money){
            throw new ApiException(ApiException::WITHDRAW_AMOUNT_INVALID);
        }

        WithdrawLogic::checkUserWithdrawLimit($user->id,$amount);

        event(new WithdrawCreate($user->id,$amount,isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'));
        self::$needRefresh = true;
        return self::createJsonData(true,['withdraw_channel'=>Setting()->get('withdraw_channel',Withdraw::WITHDRAW_CHANNEL_WX),'tips'=>'您的提现请求已受理']);
    }
}