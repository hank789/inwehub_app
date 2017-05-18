<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午3:30
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\Feedback;
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
        UserDevice::firstOrCreate(['user_id'=>$user->id,
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
        return self::createJsonData(true);
    }

    public function appVersion(){
        $ios_version = Setting()->get('ios_version','1.0.0');
        $android_version = Setting()->get('android_version','1.0.0');
        $is_force = Setting()->get('is_force','0');
        $update_msg = Setting()->get('update_msg','1、大额提现t+1到账。\n2、变现进度做了优化。\n3、修复了一些bug。');
        return self::createJsonData(true,[
            'ios_version'           => $ios_version,
            'android_version'       => $android_version,
            'is_force'              => $is_force,
            'update_msg'            => $update_msg
        ]);
    }


}