<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserLoggedOut;
use App\Events\Frontend\Auth\UserRegistered;
use App\Exceptions\ApiException;
use App\Jobs\SendPhoneMessage;
use App\Models\EmailToken;
use App\Models\LoginRecord;
use App\Models\User;
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
            'type'   => 'required'
        ];

        $this->validate($request,$validateRules);
        $mobile = $request->input('mobile');
        $type   = $request->input('type');
        if(RateLimiter::instance()->increase('register',$mobile,60,1)){
            throw new ApiException(ApiException::LIMIT_ACTION);
        }

        $code = makeVerifyCode();
        dispatch(new SendPhoneMessage($mobile,$code,$type));
    }

    //刷新token
    public function refreshToken(Request $request,JWTAuth $JWTAuth){
        try {
            $newToken = $JWTAuth->setRequest($request)->parseToken()->refresh();
        } catch (TokenExpiredException $e) {
            return self::createJsonData(false,ApiException::TOKEN_EXPIRED,'token已失效')->setStatusCode($e->getStatusCode());
        } catch (JWTException $e) {
            return self::createJsonData(false,ApiException::TOKEN_INVALID,'token无效')->setStatusCode($e->getStatusCode());
        }
        // send the refreshed token back to the client
        return static::createJsonData(true,ApiException::SUCCESS,'ok',['token'=>$newToken])->header('Authorization', 'Bearer '.$newToken);
    }

    public function login(Request $request,JWTAuth $JWTAuth){

        $validateRules = [
            'mobile' => 'required|cn_phone',
        ];

        $this->validate($request,$validateRules);

        /*只接收mobile和password的值*/
        $credentials = $request->only('mobile', 'password');

        try{
            /*根据邮箱地址和密码进行认证*/
            if ($token = $JWTAuth->attempt($credentials))
            {
                //登陆事件通知
                event(new UserLoggedIn($request->user()));
                $message = 'ok';
                if($this->credit($request->user()->id,'login',Setting()->get('coins_login'),Setting()->get('credits_login'))){
                    $message = '登陆成功! '.get_credit_message(Setting()->get('credits_login'),Setting()->get('coins_login'));
                }
                $deviceCode = $request->input('device_code');

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
                $loginrecord->device_code = $deviceCode;
                $loginrecord->user_id = $request->user()->id;
                $loginrecord->save();

                /*认证成功*/
                return static::createJsonData(true,ApiException::SUCCESS,$message,['token'=>$token]);

            }
        }catch (JWTException $e){
            return static::createJsonData(false,$e->getCode(),$e->getMessage(),[]);
        }
        return static::createJsonData(false,500,'用户名或密码错误',[])->setStatusCode(401);

    }

    /**
     * 用户注册入口
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function register(Request $request,JWTAuth $JWTAuth,Registrar $registrar)
    {

        /*注册是否开启*/
        if(!Setting()->get('register_open',1)){
            return static::createJsonData(false,403,'管理员已关闭了网站的注册功能!',[]);
        }

        /*防灌水检查*/
        if( Setting()->get('register_limit_num') > 0 ){
            $registerCount = $this->counter('register_number_'.md5($request->ip()));
            if( $registerCount > Setting()->get('register_limit_num')){
                return static::createJsonData(false,500,'您的当前的IP已经超过当日最大注册数目，如有疑问请联系管理员',[]);
            }
        }


        /*表单数据校验*/
        $validateRules = [
            'name' => 'required|min:2|max:100',
            'mobile' => 'required|cn_phone|unique:users',
            'code'   => 'required',
            'password' => 'required|min:6|max:16',
        ];

        if( Setting()->get('code_register') == 1){
            $validateRules['captcha'] = 'required|captcha';
        }

        $this->validate($request,$validateRules);
        //验证手机验证码


        $formData = $request->all();
        $formData['email'] = $formData['mobile'];
        $formData['status'] = 1;
        $formData['visit_ip'] = $request->getClientIp();

        $user = $registrar->create($formData);
        $user->attachRole(2); //默认注册为普通用户角色
        $user->userData->email_status = 1;
        $user->userData->save();
        $message = '注册成功!';
        if($this->credit($user->id,'register',Setting()->get('coins_register'),Setting()->get('credits_register'))){
            $message .= get_credit_message(Setting()->get('credits_register'),Setting()->get('coins_register'));
        }
        //注册事件通知
        event(new UserRegistered($request->user()));

        /*发送邮箱验证邮件*/

        $token = $JWTAuth->fromUser($user);
        return static::createJsonData(true,100,'ok',['token'=>$token]);
    }


    /*忘记密码*/
    public function forgetPassword(Request $request)
    {

        /*表单数据校验*/
        $this->validate($request, [
            'email' => 'required|email|exists:users',
            'captcha' => 'required|captcha'
        ]);

        $emailToken = EmailToken::create([
            'email' =>  $request->input('email'),
            'token' => EmailToken::createToken(),
            'action'=> 'findPassword'
        ]);

        if($emailToken){
            $subject = Setting()->get('website_name').' 找回密码通知';
            $content = "如果您在 ".Setting()->get('website_name')."的密码丢失，请点击下方链接找回 → ".route('auth.user.findPassword',['token'=>$emailToken->token])."<br />如非本人操作，请忽略此邮件！";
            $this->sendEmail($emailToken->email,$subject,$content);
        }
        return self::createJsonData(true,ApiException::SUCCESS,'ok');

    }


    public function resetPassword($token,Request $request,JWTAuth $JWTAuth)
    {
        $this->validate($request, [
            'password' => 'required|min:6',
            'captcha' => 'required|captcha'
        ]);

        $emailToken = EmailToken::where('action','=','findPassword')->where('token','=',$token)->first();
        if(!$emailToken){
            throw new ApiException(ApiException::TOKEN_INVALID);
        }

        if($emailToken->created_at->diffInMinutes() > 60){
            throw new ApiException(ApiException::TOKEN_EXPIRED);
        }

        $user = User::where('email','=',$emailToken->email)->first();

        if(!$user){
            throw new ApiException(ApiException::USER_NOT_FOUND);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        EmailToken::clear($user->email,'findPassword');
        $token = $JWTAuth->refresh();

        return self::createJsonData(true,ApiException::SUCCESS,'密码修改成功,请重新登录',['token'=>$token]);

    }



    /**
     * 用户登出
     */
    public function logout(Guard $auth){
        //通知
        event(new UserLoggedOut($auth->user()));
        $auth->logout();
    }



}
