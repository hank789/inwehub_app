<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午3:30
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\Feedback;
use App\Models\AppVersion;
use App\Models\UserDevice;
use Illuminate\Http\Request;


class SystemController extends Controller {

    public function feedback(Request $request)
    {
        $validateRules = [
            'content' => 'required'
        ];
        $this->validate($request, $validateRules);
        event(new Feedback($request->user(),$request->input('content')));
        return self::createJsonData(true);
    }

    public function device(Request $request){
        $validateRules = [
            'client_id' => 'required',
            'device_token' => 'required',
            'device_type'  => 'required|in:1,2'
        ];
        $this->validate($request, $validateRules);
        $data = $request->all();
        $user = $request->user();
        $user_device = UserDevice::firstOrCreate(['user_id'=>$user->id,
            'client_id'=>$data['client_id'],
            'device_type'=>$data['device_type']],
            [
                'user_id'=>$user->id,
                'client_id'=>$data['client_id'],
                'device_type'=>$data['device_type'],
                'device_token' => $data['device_token'],
                'appid'        => $data['appid'],
                'appkey'       => $data['appkey'],
                'created_at'   => date('Y-m-d H:i:s')
            ]);
        $user_device->status = 1;
        $user_device->updated_at = date('Y-m-d H:i:s');
        $user_device->save();

        return self::createJsonData(true);
    }

    public function appVersion(){
        $last = AppVersion::where('status',1)->orderBy('app_version','desc')->first();

        $app_version = $last->app_version??'1.0.0';
        $is_force = $last->is_force??0;
        $update_msg = $last->update_msg??'1、大额提现t+1到账。\n2、变现进度做了优化。\n3、修复了一些bug。';
        $package_url = $last->package_url??'http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/app_version/com.inwehub.InwehubApp.wgt';
        return self::createJsonData(true,[
            'app_version'           => $app_version,
            'is_force'              => $is_force,
            'package_url'           => $package_url,
            'update_msg'            => $update_msg
        ]);
    }

    public function getPayConfig(){
        $data = [
            "withdraw_suspend"=> Setting()->get('withdraw_suspend',0),//是否暂停提现,0否,1暂停提现
            "pay_method_weixin"=> Setting()->get('pay_method_weixin',1),//是否开启微信支付,1开启
            "pay_method_ali"=> Setting()->get('pay_method_ali',0),//是否开启阿里支付,0未开启
            "withdraw_day_limit"=> Setting()->get('withdraw_day_limit',1),//用户每天最大提现次数
            "withdraw_per_min_money"=> Setting()->get('withdraw_per_min_money',10),//用户单次最低提现金额
            "withdraw_per_max_money"=> Setting()->get('withdraw_per_max_money',2000),//用户单次最高提现金额
            "pay_settlement_cycle"=> Setting()->get('pay_settlement_cycle',5),//支付结算周期
        ];

        return self::createJsonData(true,$data);
    }


}