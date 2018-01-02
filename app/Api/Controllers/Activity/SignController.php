<?php namespace App\Api\Controllers\Activity;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Credit;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Events\Frontend\System\Credit as CreditEvent;

/**
 * @author: wanghui
 * @date: 2017/7/13 上午11:30
 * @email: wanghui@yonglibao.com
 */

class SignController extends Controller {

    //每日签到
    public function daily(Request $request){
        $user = $request->user();
        $date = date('Ymd');
        $event = 'sign:'.$user->id;
        if (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase($event,$date,86400*14)) {
            for ($i=0;$i<=7;$i++) {
                $date2 = date('Ymd',strtotime('-'.$i.' days'));
                $isSigned = RateLimiter::instance()->getValue($event,$date2);
                if ($isSigned <= 0) {
                    break;
                }
            }
            if ($i == 8) {
                //下一个签到周期
                $days = 1;
            } else {
                $days = $i;
            }
            $return = getDailySignInfo($days);
            $return['days'] = $days;
            RateLimiter::instance()->increaseBy($event,'credits',$return['credits'],0);
            event(new CreditEvent($user->id,Credit::KEY_FIRST_USER_SIGN_DAILY,$return['coins'],$return['credits'],0,'连续签到'.$days.'天'));
        } else {
            throw new ApiException(ApiException::ACTIVITY_DAILY_SIGN_REPEAT);
        }

        return self::createJsonData(true,$return);
    }

    public function dailyInfo(Request $request){
        $user = $request->user();
        $event = 'sign:'.$user->id;
        $return = [];
        for ($i=0;$i<=7;$i++) {
            $date2 = date('Ymd',strtotime('-'.$i.' days'));
            $isSigned = RateLimiter::instance()->getValue($event,$date2);
            if ($isSigned <= 0) {
                break;
            }
        }
        if ($i == 8) {
            //下一个签到周期
            $days = 1;
        } else {
            $days = $i;
        }
        for ($j=1;$j<=7;$j++) {
            $return['day'.$j] = getDailySignInfo($j);
            $return['day'.$j]['signed'] = 0;
            if ($j<=$days) $return['day'.$j]['signed'] = 1;
        }
        $return['days'] = $days;
        $return['total_credits'] = RateLimiter::instance()->getValue($event,'credits');
        $return['total_coins'] = 0;
        $return['total_money'] = 0;

        return self::createJsonData(true,$return);
    }

}