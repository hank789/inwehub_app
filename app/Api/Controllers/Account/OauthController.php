<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Models\UserOauth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;

class OauthController extends Controller
{

    public function callback($type,Request $request,JWTAuth $JWTAuth){

        $validateRules = [
            'openid' => 'required',
            'nickname' => 'required',
            'avatar'   => 'required',
            'access_token' => 'required',
            'refresh_token' => 'required',
            'expires_in' => 'required',
            'scope' => 'required',
        ];

        $this->validate($request,$validateRules);

        $data = $request->all();
        $user = null;
        $token = null;
        $user_id = null;
        try {
            $user = JWTAuth::parseToken()->authenticate();
            $token = JWTAuth::getToken();
            $user_id = $user->id;
        } catch (\Exception $e) {

        }

        $object = UserOauth::where('auth_type',$type)->where('openid',$data['openid'])->first();
        //微信登陆
        if ($object && $object->user_id && !$user) {
            $user = User::find($object->user_id);
            $token = $JWTAuth->fromUser($user);
            $user_id = $user->id;
        }

        if($object && $user && $object->user_id != $user->id){
            throw new ApiException(ApiException::USER_OAUTH_BIND_OTHERS);
        }

        $oauthData = UserOauth::updateOrCreate([
            'auth_type'=>$type,
            'user_id'=> $user_id,
            'openid'   => $data['openid']
        ],[
            'auth_type'=>$type,
            'user_id'=> $user_id,
            'openid'   => $data['openid'],
            'nickname'=>$data['nickname'],
            'avatar'=>$data['avatar'],
            'access_token'=>$data['access_token'],
            'refresh_token'=>$data['refresh_token'],
            'expires_in'=>$data['expires_in'],
            'full_info'=>isset($data['full_info']) ? json_encode($data['full_info']):'',
            'scope'=>$data['scope']
        ]);
        return self::createJsonData(true,['token'=>$token]);
    }


}