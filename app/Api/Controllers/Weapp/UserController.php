<?php namespace App\Api\Controllers\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:00
 * @email: wanghui@yonglibao.com
 */
use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Models\User;
use App\Models\UserOauth;
use App\Third\Weapp\Wxxcx;
use Illuminate\Http\Request;
use App\Services\Registrar;
use Tymon\JWTAuth\JWTAuth;

class UserController extends controller {
    protected $wxxcx;

    function __construct(Wxxcx $wxxcx)
    {
        $this->wxxcx = $wxxcx;
    }

    //小程序登录获取用户信息
    public function getWxUserInfo(Request $request,JWTAuth $JWTAuth,Registrar $registrar)
    {
        //code 在小程序端使用 wx.login 获取
        $code = request('code', '');
        //encryptedData 和 iv 在小程序端使用 wx.getUserInfo 获取
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');

        //根据 code 获取用户 session_key 等信息, 返回用户openid 和 session_key
        //ex:{"session_key":"sCKZIw/kW3Xy+3ykRmbLWQ==","expires_in":7200,"openid":"oW2D-0DjAQNvKiMqiDME5wpDdymE"}
        $userInfo = $this->wxxcx->getLoginInfo($code);

        //获取解密后的用户信息
        //ex:{\"openId\":\"oW2D-0DjAQNvKiMqiDME5wpDdymE\",\"nickName\":\"hank\",\"gender\":1,\"language\":\"zh_CN\",\"city\":\"Pudong New District\",\"province\":\"Shanghai\",\"country\":\"CN\",\"avatarUrl\":\"http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKibUNMkQ0sVd8jUPHGXia2G78608O9qs9eGAd06jeI2ZRHiaH4DbxI9ppsucxbemxuPawrBh95Sd3PA/0\",\"watermark\":{\"timestamp\":1497602544,\"appid\":\"wx5f163b8ab1c05647\"}}
        $return = $this->wxxcx->getUserInfo($encryptedData, $iv);

        \Log::info('return',$return);
        $token = '';
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEAPP)
            ->where('openid',$userInfo['openid'])->first();

        if (!$oauthData && isset($return['unionId'])) {
            $oauthData = UserOauth::whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH])
                ->where('unionid',$return['unionId'])->first();
            if ($oauthData) {
                UserOauth::create(
                    [
                        'auth_type'=>UserOauth::AUTH_TYPE_WEAPP,
                        'user_id'=> $oauthData->user_id,
                        'openid'   => $userInfo['openid'],
                        'unionid'  => $return['unionId'],
                        'nickname'=>$return['nickName'],
                        'avatar'=>$return['avatarUrl'],
                        'access_token'=>$userInfo['session_key'],
                        'refresh_token'=>'',
                        'expires_in'=>$userInfo['expires_in'],
                        'full_info'=>json_encode($return),
                        'scope'=>'authorization_code',
                        'status' => 1
                    ]
                );
            } else {
                $oauthData = UserOauth::create(
                    [
                        'auth_type'=>UserOauth::AUTH_TYPE_WEAPP,
                        'user_id'=> 0,
                        'openid'   => $userInfo['openid'],
                        'unionid'  => $return['unionId'],
                        'nickname'=>$return['nickName'],
                        'avatar'=>$return['avatarUrl'],
                        'access_token'=>$userInfo['session_key'],
                        'refresh_token'=>'',
                        'expires_in'=>$userInfo['expires_in'],
                        'full_info'=>json_encode($return),
                        'scope'=>'authorization_code',
                        'status' => 1
                    ]
                );
            }
        }
        if ($oauthData && $oauthData->user_id) {
            $user = User::find($oauthData->user_id);
            $token = $JWTAuth->fromUser($user);
            event(new UserLoggedIn($user,'小程序登陆'));
        }


        return self::createJsonData(true,['token'=>$token,'openid'=>$userInfo['openid']]);
        //$token = $JWTAuth->fromUser($user);
        //return static::createJsonData(true,['token'=>$token,'name'=>$return['nickName'],'avatarUrl'=>$return['avatarUrl']]);
    }
}