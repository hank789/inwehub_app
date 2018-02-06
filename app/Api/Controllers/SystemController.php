<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午3:30
 * @email: wanghui@yonglibao.com
 */

use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\System\FuncZan;
use App\Events\Frontend\System\SystemNotify;
use App\Models\AppVersion;
use App\Models\LoginRecord;
use App\Models\UserData;
use App\Models\UserDevice;
use App\Services\GeoHash;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Tymon\JWTAuth\JWTAuth;

class SystemController extends Controller {

    public function feedback(Request $request)
    {
        $validateRules = [
            'title'   => 'required',
            'content' => 'required'
        ];
        $this->validate($request, $validateRules);
        $user = $request->user();

        $fields = [];
        $fields[] = [
            'title'=>'内容',
            'value'=>$request->input('content')
        ];
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.$request->input('title'),$fields));
        return self::createJsonData(true);
    }

    public function applySkillTag(Request $request)
    {
        $validateRules = [
            'tag_name' => 'required'
        ];
        $this->validate($request, $validateRules);
        $user = $request->user();
        $fields = [];
        $fields[] = [
            'title'=>'标签',
            'value'=>$request->input('tag_name')
        ];
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']申请添加擅长标签',$fields));
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
        $user_device->device_token = $data['device_token'];
        $user_device->appid = $data['appid'];
        $user_device->appkey = $data['appkey'];
        $user_device->status = 1;
        $user_device->updated_at = date('Y-m-d H:i:s');
        $user_device->save();

        return self::createJsonData(true);
    }

    public function location(Request $request){
        $user = $request->user();
        $clientIp = $request->getClientIp();
        $iosPushNoticeOpen = $request->input('ios_push_notify',-1);
        $pushNotify = '';
        if ($iosPushNoticeOpen >= 0) {
            $pushNotify = ';ios推送:'.($iosPushNoticeOpen?'开启':'关闭');
        }
        //登陆事件通知
        event(new UserLoggedIn($user,$request->input('device_system').'唤起'.$pushNotify));
        if (RateLimiter::instance()->increase('user-location',$user->id.'-'.$clientIp,3600)) {
            return self::createJsonData(true);
        }
        $loginrecord = new LoginRecord();
        $loginrecord->ip = $clientIp;

        $location = $this->findIp($clientIp);
        array_filter($location);
        $loginrecord->address = trim(implode(' ', $location));
        $loginrecord->device_system = $request->input('device_system');
        $loginrecord->device_name = $request->input('device_name');
        $loginrecord->device_model = $request->input('device_model');
        $loginrecord->device_code = $request->input('device_code');
        $loginrecord->user_id = $user->id;
        $loginrecord->address_detail = $request->input('current_address_name');
        $loginrecord->longitude = $request->input('current_address_longitude');
        $loginrecord->latitude = $request->input('current_address_latitude');
        $loginrecord->save();
        UserData::where('user_id',$user->id)->update([
            'last_visit' => Carbon::now(),
            'last_login_ip' => $clientIp,
            'longitude'    => $loginrecord->longitude,
            'latitude'     => $loginrecord->latitude,
            'geohash'      => $loginrecord->longitude?GeoHash::instance()->encode($loginrecord->latitude,$loginrecord->longitude):''
        ]);
        return self::createJsonData(true);
    }

    public function appVersion(Request $request){
        $app_uuid = $request->input('app_uuid');
        if($app_uuid && RateLimiter::instance()->increase('system:getAppVersion',$app_uuid,5,1)){
            return self::createJsonData(true,[
                'app_version'           => 0,
                'is_ios_force'          => 0,
                'is_android_force'      => 0,
                'package_url'           => '',
                'update_msg'            => '',
                'ios_force_update_url'  => '',
                'android_force_update_url' => ''
            ]);
        }
        $last = AppVersion::where('status',1)->orderBy('app_version','desc')->first();

        $ios_force_update_url = 'https://www.pgyer.com/FLBT';
        $android_force_update_url = 'https://www.pgyer.com/mpKs';

        if(config('app.env') == 'production'){
            $ios_force_update_url = 'itms-apps://itunes.apple.com/cn/app/inwehub/id1244660980?l=zh&mt=8';//正式环境换成苹果商店的地址
            $android_force_update_url = 'market://details?id=com.inwehub.InwehubApp';//正式环境换成android商店的地址
        }
        $app_version = $last->app_version??'1.0.0';
        $is_ios_force = $last->is_ios_force??0;
        $is_android_force = $last->is_android_force??0;
        $update_msg = $last->update_msg??'1、大额提现t+1到账。\n2、变现进度做了优化。\n3、修复了一些bug。';
        $package_url = $last->package_url??'http://intervapp-test.oss-cn-zhangjiakou.aliyuncs.com/app_version/com.inwehub.InwehubApp.wgt';
        return self::createJsonData(true,[
            'app_version'           => $app_version,
            'is_ios_force'          => $is_ios_force,
            'is_android_force'      => $is_android_force,
            'package_url'           => $package_url,
            'update_msg'            => $update_msg,
            'ios_force_update_url'  => $ios_force_update_url,
            'android_force_update_url' => $android_force_update_url
        ]);
    }

    public function getAppMarketUrl(){
        $data = [
            'ios_url' => 'https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewContentsUserReviews?id=1244660980&pageNumber=0&sortOrdering=2&type=Purple+Software&mt=8',
            'android_url' => 'market://details?id=com.inwehub.InwehubApp'
        ];

        return self::createJsonData(true,$data);
    }

    public function getPayConfig(Request $request,JWTAuth $JWTAuth){
        try {
            $user = $JWTAuth->parseToken()->authenticate();
            $user_total_money = $user->getAvailableTotalMoney();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user_total_money = 0;
        }
        $config = get_pay_config();
        $config['user_total_money'] = $user_total_money;
        return self::createJsonData(true,$config);
    }

    public function htmlToImage(Request $request){
        $validateRules = [
            'html' => 'required'
        ];
        $this->validate($request, $validateRules);
        $data = $request->all();
        $filename = time().str_random(7).'.jpeg';
        if (filter_var($data['html'], FILTER_VALIDATE_URL)) {
            Browsershot::url($data['html'])->save($filename);
        } else {
            Browsershot::html($data['html'])->save($filename);
        }
        return self::createJsonData(true,['image'=>base64_encode(file_get_contents($filename))]);
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

    //启动页
    public function bootGuide(){
        return self::createJsonData(true,['show_guide'=>1]);
    }

}