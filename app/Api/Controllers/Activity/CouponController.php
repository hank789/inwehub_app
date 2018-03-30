<?php namespace App\Api\Controllers\Activity;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Logic\MoneyLogLogic;
use App\Models\Activity\Coupon;
use App\Models\Pay\MoneyLog;
use App\Models\User;
use App\Services\RateLimiter;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/7/13 上午11:30
 * @email: wanghui@yonglibao.com
 */

class CouponController extends Controller {

    public function getCoupon(Request $request){
        $validateRules = [
            'coupon_type'    => 'required|integer'
        ];
        $this->validate($request,$validateRules);
        $user = $request->user();
        $data = $request->all();
        $coupon_value = 0;
        $coupon_value_type = 1;
        $coupon_description = '';
        switch($data['coupon_type']){
            case Coupon::COUPON_TYPE_FIRST_ASK:
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_FIRST_ASK)->first();
                if(!$coupon){
                    Coupon::create([
                        'user_id' => $user->id,
                        'coupon_type' => Coupon::COUPON_TYPE_FIRST_ASK,
                        'coupon_value' => 0,
                        'coupon_status' => Coupon::COUPON_STATUS_PENDING,
                        'expire_at' => date('Y-m-d H:i:s',strtotime('+72 hours')),
                        'days' => 3
                    ]);
                } else {
                    if ($coupon->coupon_status == Coupon::COUPON_STATUS_EXPIRED) {
                        $coupon->coupon_status = Coupon::COUPON_STATUS_PENDING;
                    }
                    $coupon->expire_at = date('Y-m-d H:i:s',strtotime('+72 hours'));
                    $coupon->save();
                }
                break;
            case Coupon::COUPON_TYPE_DAILY_SIGN_SMALL:
                $coupon_value = rand(1,2);
                $expire_at = date('Y-m-d 23:59:59');
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_DAILY_SIGN_SMALL)->where('expire_at',$expire_at)->first();
                if(!$coupon){
                    $coupon = Coupon::create([
                        'user_id' => $user->id,
                        'coupon_type' => Coupon::COUPON_TYPE_DAILY_SIGN_SMALL,
                        'coupon_value' => $coupon_value,
                        'coupon_status' => Coupon::COUPON_STATUS_USED,
                        'expire_at' => $expire_at,
                        'used_at' => date('Y-m-d H:i:s'),
                        'days' => 1
                    ]);
                    MoneyLogLogic::addMoney($user->id,$coupon_value,MoneyLog::MONEY_TYPE_COUPON,$coupon,0);
                    RateLimiter::instance()->increaseBy('sign:'.$user->id,'money',$coupon_value,0);
                }
                break;
            case Coupon::COUPON_TYPE_DAILY_SIGN_BIG:
                $coupon_value = rand(1,3);
                $expire_at = date('Y-m-d 23:59:59');
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_DAILY_SIGN_BIG)->where('expire_at',$expire_at)->first();
                if(!$coupon){
                    $coupon = Coupon::create([
                        'user_id' => $user->id,
                        'coupon_type' => Coupon::COUPON_TYPE_DAILY_SIGN_BIG,
                        'coupon_value' => $coupon_value,
                        'coupon_status' => Coupon::COUPON_STATUS_USED,
                        'expire_at' => $expire_at,
                        'used_at' => date('Y-m-d H:i:s'),
                        'days' => 1
                    ]);
                    MoneyLogLogic::addMoney($user->id,$coupon_value,MoneyLog::MONEY_TYPE_COUPON,$coupon,0);
                    RateLimiter::instance()->increaseBy('sign:'.$user->id,'money',$coupon_value,0);
                }
                break;
            case Coupon::COUPON_TYPE_NEW_REGISTER_INVITATION:
                $rcUser = User::find($user->rc_uid);
                if (!$rcUser) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
                $coupon_value = rand(1,3);
                $coupon_description = $rcUser->name.'送你';
                $expire_at = date('Y-m-d 23:59:59');
                $coupon = Coupon::where('user_id',$user->id)->where('coupon_type',Coupon::COUPON_TYPE_NEW_REGISTER_INVITATION)->first();
                if(!$coupon){
                    $coupon = Coupon::create([
                        'user_id' => $user->id,
                        'coupon_type' => Coupon::COUPON_TYPE_NEW_REGISTER_INVITATION,
                        'coupon_value' => $coupon_value,
                        'coupon_status' => Coupon::COUPON_STATUS_USED,
                        'expire_at' => $expire_at,
                        'used_at' => date('Y-m-d H:i:s'),
                        'days' => 1
                    ]);
                    MoneyLogLogic::addMoney($user->id,$coupon_value,MoneyLog::MONEY_TYPE_COUPON,$coupon,0);
                }
                break;
        }
        return self::createJsonData(true,['tip'=>'领取成功','coupon_description'=>$coupon_description,'coupon_type'=>$data['coupon_type'],'coupon_value_type'=>$coupon_value_type,'coupon_value'=>$coupon_value]);

    }

}