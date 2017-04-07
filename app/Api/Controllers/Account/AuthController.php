<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserLoggedOut;
use App\Events\Frontend\Auth\UserRegistered;
use App\Exceptions\ApiException;
use App\Models\EmailToken;
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
use Tymon\JWTAuth\JWTAuth;


class AuthController extends Controller
{

    //发送手机验证码
    public function sendPhoneCode(Request $request)
    {
        $phone = $request->input('phone');
        if(RateLimiter::instance()->increase('register',$phone,60,1)){
            throw new ApiException(ApiException::LIMIT_ACTION);
        }


    }

    public function login(Request $request,JWTAuth $JWTAuth){

        /*只接收email和password的值*/
        $credentials = $request->only('email', 'password');

        try{
            /*根据邮箱地址和密码进行认证*/
            if ($token = $JWTAuth->attempt($credentials))
            {
                //登陆事件通知
                event(new UserLoggedIn($request->user()));
                if($this->credit($request->user()->id,'login',Setting()->get('coins_login'),Setting()->get('credits_login'))){
                    $message = '登陆成功! '.get_credit_message(Setting()->get('credits_login'),Setting()->get('coins_login'));
                    return static::createJsonData(true,ApiException::SUCCESS,$message,['token'=>$token]);
                }

                /*认证成功*/
                return static::createJsonData(true,ApiException::SUCCESS,'ok',['token'=>$token]);

            }
        }catch (JWTException $e){
            return static::createJsonData(false,$e->getCode(),$e->getMessage(),[]);
        }
        return static::createJsonData(false,500,'用户名或密码错误',[]);

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
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6|max:16',
        ];

        if( Setting()->get('code_register') == 1){
            $validateRules['captcha'] = 'required|captcha';
        }

        $this->validate($request,$validateRules);

        $formData = $request->all();
        $formData['status'] = 0;
        $formData['visit_ip'] = $request->getClientIp();

        $user = $registrar->create($formData);
        $user->attachRole(2); //默认注册为普通用户角色
        $message = '注册成功!';
        if($this->credit($user->id,'register',Setting()->get('coins_register'),Setting()->get('credits_register'))){
            $message .= get_credit_message(Setting()->get('credits_register'),Setting()->get('coins_register'));
        }
        //注册事件通知
        event(new UserRegistered($request->user()));

        /*发送邮箱验证邮件*/

        $emailToken = EmailToken::create([
            'email' => $user->email,
            'token' => EmailToken::createToken(),
            'action'=> 'register'
        ]);

        if($emailToken){
            $subject = '欢迎注册'.Setting()->get('website_name').',请激活您注册的邮箱！';
            $content = "「".$user->name."」您好，请激活您在 ".Setting()->get('website_name')." 的注册邮箱！<br /> 请在1小时内点击该链接激活注册账号 → ".route('auth.email.verifyToken',['action'=>$emailToken->action,'token'=>$emailToken->token])."<br />如非本人操作，请忽略此邮件！";
            $this->sendEmail($emailToken->email,$subject,$content);
        }

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
            throw new ApiException(ApiException::TOKEN_MISSING);
        }

        if($emailToken->created_at->diffInMinutes() > 60){
            throw new ApiException(ApiException::TOKEN_INVALID);
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
