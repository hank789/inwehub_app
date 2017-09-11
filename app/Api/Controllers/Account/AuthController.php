<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserLoggedOut;
use App\Events\Frontend\Auth\UserRegistered;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Models\Credit;
use App\Models\LoginRecord;
use App\Models\Readhub\ReadHubUser;
use App\Models\Tag;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserOauth;
use App\Models\UserRegistrationCode;
use App\Services\RateLimiter;
use App\Services\Registrar;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\JWTAuth;


class AuthController extends Controller
{

    //发送手机验证码
    public function sendPhoneCode(Request $request)
    {
        $validateRules = [
            'mobile' => 'required|cn_phone',
            'type'   => 'required|in:register,login,change,wx_gzh_register'
        ];

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        $type   = $request->input('type');
        if(RateLimiter::instance()->increase('sendPhoneCode:'.$type,$mobile,120,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        $user = User::where('mobile',$mobile)->first();
        switch($type){
            case 'register':
                if($user){
                    throw new ApiException(ApiException::USER_PHONE_EXIST);
                }
                if(Setting()->get('registration_code_open',1)){
                    $rcode = UserRegistrationCode::where('code',$request->input('registration_code',''))->where('status',UserRegistrationCode::CODE_STATUS_PENDING)->first();
                    if(empty($rcode)){
                        throw new ApiException(ApiException::USER_REGISTRATION_CODE_INVALID);
                    }
                }
                break;
            case 'wx_gzh_register':
                $code = $request->input('registration_code','');
                if($user){

                } else {
                    if(Setting()->get('registration_code_open',1)){
                        if($code){
                            $rcode = UserRegistrationCode::where('code',$code)->first();
                            if(empty($rcode)){
                                throw new ApiException(ApiException::USER_REGISTRATION_CODE_INVALID);
                            }
                            if($rcode->status == UserRegistrationCode::CODE_STATUS_EXPIRED){
                                throw new ApiException(ApiException::USER_REGISTRATION_CODE_EXPIRED);
                            }
                            if($rcode->status != UserRegistrationCode::CODE_STATUS_PENDING){
                                throw new ApiException(ApiException::USER_REGISTRATION_CODE_USED);
                            }
                        } else {
                            throw new ApiException(ApiException::USER_WEIXIN_REGISTER_NEED_CODE);
                        }
                    }
                }
                break;
            default:
                if(!$user){
                    throw new ApiException(ApiException::USER_NOT_FOUND);
                }
                break;
        }

        $code = makeVerifyCode();
        dispatch((new SendPhoneMessage($mobile,$code,$type)));
        return self::createJsonData(true);
    }

    //刷新token
    public function refreshToken(Request $request,JWTAuth $JWTAuth){
        try {
            $newToken = $JWTAuth->setRequest($request)->parseToken()->refresh();
        } catch (TokenExpiredException $e) {
            return self::createJsonData(false,[],ApiException::TOKEN_EXPIRED,'token已失效')->setStatusCode($e->getStatusCode());
        } catch (JWTException $e) {
            return self::createJsonData(false,[],ApiException::TOKEN_INVALID,'token无效')->setStatusCode($e->getStatusCode());
        }
        // send the refreshed token back to the client
        return static::createJsonData(true,['token'=>$newToken],ApiException::SUCCESS,'ok')->header('Authorization', 'Bearer '.$newToken);
    }

    public function login(Request $request,JWTAuth $JWTAuth){

        $validateRules = [
            'mobile' => 'required|cn_phone',
            'password' => 'required'
        ];

        $this->validate($request,$validateRules);

        /*只接收mobile和password的值*/
        $credentials = $request->only('mobile', 'password');
        if(RateLimiter::instance()->increase('userLogin',$credentials['mobile'],3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        /*根据邮箱地址和密码进行认证*/
        if ($token = $JWTAuth->attempt($credentials))
        {

            $user = $request->user();
            $device_code = $request->input('device_code');
            if($user->last_login_token && $device_code){
                try {
                    $JWTAuth->refresh($user->last_login_token);
                } catch (\Exception $e){
                    \Log::error($e->getMessage());
                }
            }
            $user->last_login_token = $token;
            $user->save();
            if($user->status != 1) {
                throw new ApiException(ApiException::USER_SUSPEND);
            }
            //登陆事件通知
            event(new UserLoggedIn($user));
            $message = 'ok';
            if($this->credit($user->id,Credit::KEY_LOGIN)){
                $message = '登陆成功! ';
            }
            // 登录记录
            $clientIp = $request->getClientIp();
            $loginrecord = new LoginRecord();
            $loginrecord->ip = $clientIp;

            $location = $this->findIp($clientIp);
            array_filter($location);
            $loginrecord->address = trim(implode(' ', $location));
            $loginrecord->device_system = $request->input('device_system');
            $loginrecord->device_name = $request->input('device_name');
            $loginrecord->device_model = $request->input('device_model');
            $loginrecord->device_code = $device_code;
            $loginrecord->user_id = $user->id;
            $loginrecord->save();

            $info = [];
            $info['token'] = $token;
            $info['id'] = $user->id;
            $info['name'] = $user->name;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
            $info['avatar_url'] = $user->getAvatarUrl();
            $info['gender'] = $user->gender;
            $info['birthday'] = $user->birthday;
            $info['province'] = $user->province;
            $info['city'] = $user->city;
            $info['company'] = $user->company;
            $info['title'] = $user->title;
            $info['description'] = $user->description;
            $info['status'] = $user->status;
            $info['address_detail'] = $user->address_detail;
            $info['industry_tags'] = array_column($user->industryTags(),'name');
            $info['tags'] = Tag::whereIn('id',$user->userTag()->pluck('tag_id'))->pluck('name');

            /*认证成功*/
            return static::createJsonData(true,$info,ApiException::SUCCESS,$message);

        }

        return static::createJsonData(false,[],ApiException::USER_PASSWORD_ERROR,'用户名或密码错误');

    }

    //app注册
    public function register(Request $request,JWTAuth $JWTAuth,Registrar $registrar)
    {

        /*注册是否开启*/
        if(!Setting()->get('register_open',1)){
            return static::createJsonData(false,[],403,'管理员已关闭了网站的注册功能!');
        }

        /*防灌水检查*/
        if( Setting()->get('register_limit_num') > 0 ){
            $registerCount = $this->counter('register_number_'.md5($request->ip()));
            if( $registerCount > Setting()->get('register_limit_num')){
                return static::createJsonData(false,[],500,'您的当前的IP已经超过当日最大注册数目，如有疑问请联系管理员');
            }
        }

        /*表单数据校验*/
        $validateRules = [
            'name' => 'required|min:2|max:100',
            'mobile' => 'required|cn_phone',
            'code'   => 'required',
            'password' => 'required|min:6|max:64',
        ];
        //是否开启了邀请码注册
        if(Setting()->get('registration_code_open',1)){
            $validateRules['registration_code'] = 'required';
        }

        /*if( Setting()->get('code_register') == 1){
            $validateRules['captcha'] = 'required|captcha';
        }*/

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        if(RateLimiter::instance()->increase('userRegister',$mobile,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }
        //验证手机验证码
        $code_cache = Cache::get(SendPhoneMessage::getCacheKey('register',$mobile));
        $code = $request->input('code');
        if($code_cache != $code){
            throw new ApiException(ApiException::ARGS_YZM_ERROR);
        }

        $user = User::where('mobile',$mobile)->first();
        if($user){
            throw new ApiException(ApiException::USER_PHONE_EXIST);
        }
        if(Setting()->get('registration_code_open',1)){
            $rcode = UserRegistrationCode::where('code',$request->input('registration_code'))->where('status',UserRegistrationCode::CODE_STATUS_PENDING)->first();
            if(empty($rcode)){
                throw new ApiException(ApiException::USER_REGISTRATION_CODE_INVALID);
            }
            if($rcode->expired_at && strtotime($rcode->expired_at) < time()){
                throw new ApiException(ApiException::USER_REGISTRATION_CODE_OVERTIME);
            }
        }

        $formData = $request->all();
        $formData['email'] = null;
        if(Setting()->get('register_need_confirm', 0)){
            //注册完成后需要审核
            $formData['status'] = 0;
        }else{
            $formData['status'] = 1;
        }
        $formData['visit_ip'] = $request->getClientIp();

        $user = $registrar->create($formData);
        $user->attachRole(2); //默认注册为普通用户角色
        $user->userData->email_status = 1;
        $user->userData->save();
        if(isset($rcode)){
            $rcode->status = UserRegistrationCode::CODE_STATUS_USED;
            $rcode->register_uid = $user->id;
            $rcode->save();
        }
        $message = '注册成功!';
        $this->credit($user->id,Credit::KEY_REGISTER);

        // read站点同步注册用户
        ReadHubUser::initUser($user);

        //注册事件通知
        event(new UserRegistered($user));

        $token = $JWTAuth->fromUser($user);
        return static::createJsonData(true,['token'=>$token],ApiException::SUCCESS,$message);
    }

    //微信公众号检查手机号和微信id
    public function checkWeiXinGzh(Request $request,JWTAuth $JWTAuth)
    {

        /*表单数据校验*/
        $validateRules = [
            'mobile' => 'required|cn_phone',
            'code'   => 'required',
            'openid' => 'required'
        ];

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        if(RateLimiter::instance()->increase('userRegister',$mobile,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        //验证手机验证码
        $code_cache = Cache::get(SendPhoneMessage::getCacheKey('wx_gzh_register',$mobile));
        $code = $request->input('code');
        if($code_cache != $code){
            throw new ApiException(ApiException::ARGS_YZM_ERROR);
        }
        $registration_code = $request->input('registration_code');
        $openid = $request->input('openid');
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
            ->where('openid',$openid)->first();
        if (!$oauthData){
            throw new ApiException(ApiException::USER_WEIXIN_UNOAUTH);
        }
        $user = User::where('mobile',$mobile)->first();

        //如果用户不存在,验证邀请码,走注册流程
        if(!$user){
            if (Setting()->get('registration_code_open',1) && $registration_code) {
                $rcode = UserRegistrationCode::where('code',$registration_code)->where('status',UserRegistrationCode::CODE_STATUS_PENDING)->first();
                if(empty($rcode)){
                    throw new ApiException(ApiException::USER_REGISTRATION_CODE_INVALID);
                }
                if($rcode->expired_at && strtotime($rcode->expired_at) < time()){
                    throw new ApiException(ApiException::USER_REGISTRATION_CODE_OVERTIME);
                }
            }
        }

        //如果此微信号尚未关联用户且对应手机号用户已注册,将此微信号与用户作关联
        if ($oauthData->user_id == 0 && $user){
            $oauthData->user_id = $user->id;
            $oauthData->save();
            $token = $JWTAuth->fromUser($user);
            return static::createJsonData(true,['token'=>$token]);
        }

        //如果此微信号尚未关联,且对应手机号不存在,走注册流程
        if ($oauthData->user_id == 0 && !$user) {
            return static::createJsonData(true,['token'=>''],ApiException::USER_WEIXIN_NEED_REGISTER,'注册下一步');
        }

        //如果此微信号已绑定用户
        if($oauthData->user_id && $user){
            $token = $JWTAuth->fromUser($user);
            return static::createJsonData(true,['token'=>$token]);
        }
        throw new ApiException(ApiException::BAD_REQUEST);
    }

    public function registerWeiXinGzh(Request $request,JWTAuth $JWTAuth,Registrar $registrar){
        /*表单数据校验*/
        $validateRules = [
            'mobile' => 'required|cn_phone',
            'name' => 'required|min:2|max:100',
            'code'   => 'required',
            'password' => 'required|min:6|max:64',
            'openid' => 'required'
        ];
        //是否开启了邀请码注册
        if(Setting()->get('registration_code_open',1)){
            $validateRules['registration_code'] = 'required|min:4';
        }

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        if(RateLimiter::instance()->increase('userRegister',$mobile,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        //验证手机验证码
        $code_cache = Cache::get(SendPhoneMessage::getCacheKey('wx_gzh_register',$mobile));
        $code = $request->input('code');
        if($code_cache != $code){
            throw new ApiException(ApiException::ARGS_YZM_ERROR);
        }
        $registration_code = $request->input('registration_code');
        $openid = $request->input('openid');
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEIXIN_GZH)
            ->where('openid',$openid)->first();
        if (!$oauthData){
            throw new ApiException(ApiException::USER_WEIXIN_UNOAUTH);
        }
        //已经绑定用户了,不可能到这一步
        if ($oauthData->user_id){
            throw new ApiException(ApiException::BAD_REQUEST);
        }
        $user = User::where('mobile',$mobile)->first();
        //用户不能已存在
        if ($user) {
            throw new ApiException(ApiException::USER_PHONE_EXIST);
        }
        //如果有邀请码,则走注册流程,验证邀请码
        if(Setting()->get('registration_code_open',1) && $registration_code){
            $rcode = UserRegistrationCode::where('code',$registration_code)->where('status',UserRegistrationCode::CODE_STATUS_PENDING)->first();
            if(empty($rcode)){
                throw new ApiException(ApiException::USER_REGISTRATION_CODE_INVALID);
            }
            if($rcode->expired_at && strtotime($rcode->expired_at) < time()){
                throw new ApiException(ApiException::USER_REGISTRATION_CODE_OVERTIME);
            }
        }


        $formData = $request->all();
        $formData['email'] = null;
        if(Setting()->get('register_need_confirm', 0)){
            //注册完成后需要审核
            $formData['status'] = 0;
        }else{
            $formData['status'] = 1;
        }
        $formData['visit_ip'] = $request->getClientIp();
        $formData['source'] = User::USER_SOURCE_WEIXIN_GZH;

        $user = $registrar->create($formData);
        $user->attachRole(2); //默认注册为普通用户角色
        $user->userData->email_status = 1;
        $user->userData->save();
        $user->avatar = $oauthData->avatar;
        $user->save();
        if(isset($rcode)){
            $rcode->status = UserRegistrationCode::CODE_STATUS_USED;
            $rcode->register_uid = $user->id;
            $rcode->save();
        }
        $oauthData->user_id = $user->id;
        $oauthData->save();
        $message = '注册成功!';
        $this->credit($user->id,Credit::KEY_REGISTER);

        // read站点同步注册用户
        ReadHubUser::initUser(User::find($user->id));

        //注册事件通知
        event(new UserRegistered($user));

        $token = $JWTAuth->fromUser($user);
        return static::createJsonData(true,['token'=>$token],ApiException::SUCCESS,$message);
    }

        /*忘记密码*/
    public function forgetPassword(Request $request)
    {

        /*表单数据校验*/
        $this->validate($request, [
            'mobile' => 'required|cn_phone',
            'code' => 'required',
            'password' => 'required|min:6|max:64',
        ]);
        $mobile = $request->input('mobile');

        $user = User::where('mobile',$mobile)->first();
        if(!$user){
            throw new ApiException(ApiException::USER_NOT_FOUND);
        }

        //验证手机验证码
        $code_cache = Cache::get(SendPhoneMessage::getCacheKey('change',$mobile));
        $code = $request->input('code');
        if($code_cache != $code){
            throw new ApiException(ApiException::ARGS_YZM_ERROR);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return self::createJsonData(true);

    }



    /**
     * 用户登出
     */
    public function logout(Request $request,Guard $auth){
        //通知
        event(new UserLoggedOut($auth->user()));
        $data = $request->all();
        UserDevice::where('user_id',$auth->user()->id)->where('client_id',$data['client_id'])->where('device_type',$data['device_type'])->update(['status'=>0]);
        return self::createJsonData(true);
    }


}
