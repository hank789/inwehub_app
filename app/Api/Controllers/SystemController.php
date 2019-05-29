<?php namespace App\Api\Controllers;
/**
 * @author: wanghui
 * @date: 2017/4/21 下午3:30
 * @email: hank.huiwang@gmail.com
 */

use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\System\FuncZan;
use App\Events\Frontend\System\ImportantNotify;
use App\Events\Frontend\System\SystemNotify;
use App\Models\AppVersion;
use App\Models\LoginRecord;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Models\UserData;
use App\Models\UserDevice;
use App\Services\GeoHash;
use App\Services\RateLimiter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\JWTAuth;

class SystemController extends Controller {

    public function feedback(Request $request, JWTAuth $JWTAuth)
    {
        $validateRules = [
            'title'   => 'required',
            'content' => 'required'
        ];
        $this->validate($request, $validateRules);
        $source = '';
        if ($request->input('inwehub_user_device') == 'weapp_dianping') {
            $source = '小程序';
            $oauth = $JWTAuth->parseToken()->toUser();
            if ($oauth->user_id) {
                $user = $oauth->user;
            } else {
                $user = new \stdClass();
                $user->id = 0;
                $user->name = $oauth->nickname;
            }
        } else {
            $user = $request->user();
        }

        $fields = [];
        $fields[] = [
            'title'=>'内容',
            'value'=>$request->input('content')
        ];
        event(new ImportantNotify($source.'用户'.$user->id.'['.$user->name.']'.$request->input('title'),$fields));
        return self::createJsonData(true);
    }

    public function getOperators(Request $request, JWTAuth $JWTAuth) {
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $uid = $request->input('token');
            if (!$uid) {
                return self::createJsonData(false);
            }
            $user = User::where('uuid',$uid)->first();
            if (!$user) {
                return self::createJsonData(false);
            }
        }
        if (!$user->isRole('operatormanager') && !$user->isRole('operatorrobot')) {
            return self::createJsonData(false);
        }
        $role1 = Role::where('slug','operatorrobot')->first();
        $role2 = Role::where('slug','operatormanager')->first();
        $roleUsers = RoleUser::whereIn('role_id',[$role1->id,$role2->id])->get();
        $return = [];
        foreach ($roleUsers as $roleUser) {
            $return[] = [
                'id' => $roleUser->user_id,
                'name'=>$roleUser->user->name
            ];
        }
        return self::createJsonData(true,$return);
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
            'device_type'  => 'required|in:1,2'
        ];
        $this->validate($request, $validateRules);
        $data = $request->all();
        if (empty($data['client_id']) || empty($data['device_token'])) return self::createJsonData(true);
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
        UserDevice::where('client_id', $user_device->client_id)->where('device_token',$user_device->device_token)->update(['status'=>0]);
        $user_device->device_token = $data['device_token'];
        $user_device->appid = $data['appid'];
        $user_device->appkey = $data['appkey'];
        $user_device->status = 1;
        $user_device->updated_at = date('Y-m-d H:i:s');
        $user_device->save();


        return self::createJsonData(true);
    }

    public function activityNotify(Request $request, JWTAuth $JWTAuth) {
        try {
            $user = $JWTAuth->parseToken()->authenticate();
        } catch (\Exception $e) {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $iosPushNoticeOpen = $request->input('ios_push_notify',-1);
        $type = $request->input('type','login');
        $pushNotify = '';
        if ($iosPushNoticeOpen >= 0) {
            $pushNotify = ';ios推送:'.($iosPushNoticeOpen?'开启':'关闭');
        }
        switch ($type) {
            case 'login':
                event(new UserLoggedIn($user,$request->input('device_system').'唤起'.$pushNotify));
                break;
        }
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
        $current_version = $request->input('current_version');
        $last = AppVersion::where('status',1)->orderBy('app_version','desc')->first();
        if(($app_uuid && RateLimiter::instance()->increase('system:getAppVersionLimit',$app_uuid,5,1)) || ($app_uuid && RateLimiter::instance()->increase('system:getAppVersion',$app_uuid,60 * 60 * 2,1) && $current_version==$last->app_version)){
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


        $ios_force_update_url = 'https://www.pgyer.com/FLBT';
        $android_force_update_url = 'https://www.pgyer.com/mpKs';

        if(config('app.env') == 'production'){
            $ios_force_update_url = 'itms-apps://itunes.apple.com/cn/app/inwehub/id1244660980?l=zh&mt=8';//正式环境换成苹果商店的地址
            //https://a.app.qq.com/o/simple.jsp?pkgname=com.inwehub.InwehubApp
            //market://details?id=com.inwehub.InwehubApp
            $android_force_update_url = 'market://details?id=com.inwehub.InwehubApp';//正式环境换成android商店的地址
        }
        $app_version = $last->app_version??'1.0.0';
        $is_ios_force = $last->is_ios_force??0;
        $is_android_force = $last->is_android_force??0;
        $update_msg = '';
        $msgArr = explode("\n",$last->update_msg);
        foreach ($msgArr as $item) {
            $update_msg = $update_msg.'<p style="text-align:left">'.$item.'</p>';
        }
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
            $user->name = '游客';
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
        $snappy = App::make('snappy.image');
        $snappy->setOption('width',1125);
        if (filter_var($data['html'], FILTER_VALIDATE_URL)) {
            $filename = $snappy->getOutput($data['html']);
        } else {
            $filename = $snappy->getOutputFromHtml($data['html']);
        }
        return self::createJsonData(true,['image'=>base64_encode($filename)]);
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
        return self::createJsonData(true,['show_guide'=>Setting()->get('show_boot_guide',1)?true:false]);
    }

}