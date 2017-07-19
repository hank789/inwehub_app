<?php namespace App\Api\Controllers\Activity;
use App\Api\Controllers\Controller;
use App\Models\Activity\Coupon;
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
                }
                break;
        }
        return self::createJsonData(true,['tip'=>'领取成功','coupon_type'=>$data['coupon_type']]);

    }

}