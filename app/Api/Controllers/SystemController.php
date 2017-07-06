<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午3:30
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\System\Feedback;
use App\Events\Frontend\System\FuncZan;
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

    public function funcZan(Request $request)
    {
        $validateRules = [
            'content' => 'required'
        ];
        $this->validate($request, $validateRules);
        event(new FuncZan($request->user(),$request->input('content')));
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
        //将该用户所有类型的设备置为不可用状态
        UserDevice::where('user_id',$user->id)->update(['status'=>0]);
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

        $ios_force_update_url = 'https://www.pgyer.com/Zoy3';
        $android_force_update_url = 'https://www.pgyer.com/hfkG';

        if(config('app.env') == 'production'){
            $ios_force_update_url = 'https://www.pgyer.com/Zoy3';//正式环境换成苹果商店的地址
            $android_force_update_url = 'https://www.pgyer.com/s9AN';//正式环境换成android商店的地址
        }
        $app_version = $last->app_version??'1.0.0';
        $is_force = $last->is_force??0;
        $update_msg = $last->update_msg??'1、大额提现t+1到账。\n2、变现进度做了优化。\n3、修复了一些bug。';
        $package_url = $last->package_url??'http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/app_version/com.inwehub.InwehubApp.wgt';
        return self::createJsonData(true,[
            'app_version'           => $app_version,
            'is_force'              => $is_force,
            'package_url'           => $package_url,
            'update_msg'            => $update_msg,
            'ios_force_update_url'  => $ios_force_update_url,
            'android_force_update_url' => $android_force_update_url
        ]);
    }

    public function getAppMarketUrl(){
        $data = [
            'ios_url' => 'https://itunes.apple.com/cn/app/jie-zou-da-shi/id493901993?mt=8',
            'android_url' => 'https://itunes.apple.com/cn/app/jie-zou-da-shi/id493901993?mt=8'
        ];

        return self::createJsonData(true,$data);
    }

    public function getPayConfig(){
        return self::createJsonData(true,get_pay_config());
    }

    //服务条款
    public function serviceRegister(){
        $data = [
            'html' => Setting()->get('register_license','')
        ];

        return self::createJsonData(true,$data);
    }

    //关于我们
    public function serviceAbout(){
        $data = [
            'html' => Setting()->get('about_us','')
        ];

        return self::createJsonData(true,$data);
    }

    //常见问题
    public function serviceHelp(){
        $data = [
            'html' => Setting()->get('app_help','')
        ];

        return self::createJsonData(true,$data);
    }

    //提问帮助页
    public function serviceQaHelp(){
        $data = [
            'html' => Setting()->get('app_qa_help','')
        ];

        return self::createJsonData(true,$data);
    }

}