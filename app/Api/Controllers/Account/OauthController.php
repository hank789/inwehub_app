<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Models\UserOauth;
use Illuminate\Http\Request;

class OauthController extends Controller
{

    public function callback($type,Request $request){

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
        \Log::info('hanktest',$data);
        $user = $request->user();

        $oauthData = UserOauth::updateOrCreate([
            'auth_type'=>$type,
            'user_id'=> $user->id,
            'openid'   => $data['openid']
        ],[
            'auth_type'=>$type,
            'user_id'=> $user->id,
            'openid'   => $data['openid'],
            'nickname'=>$data['nickname'],
            'avatar'=>$data['avatar'],
            'access_token'=>$data['access_token'],
            'refresh_token'=>$data['refresh_token'],
            'expires_in'=>$data['expires_in'],
            'full_info'=>$data['full_info']??'',
            'scope'=>$data['scope']
        ]);
        return self::createJsonData(true);
    }

    public function unbind( $type , Request $request){
        $request->user()->userOauth()->where('auth_type','=',$type)->delete();
        return $this->success( route('auth.profile.oauth') , $type .'已解除绑定！');
    }


}