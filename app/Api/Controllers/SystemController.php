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


}