<?php namespace App\Api\Controllers\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:00
 * @email: hank.huiwang@gmail.com
 */
use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserRegistered;
use App\Exceptions\ApiException;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Services\RateLimiter;
use App\Third\Weapp\WeApp;
use Illuminate\Http\Request;
use App\Services\Registrar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\JWTAuth;
use App\Events\Frontend\System\SystemNotify;
class UserController extends controller {

    //小程序登录获取用户信息
    public function getWxUserInfo(Request $request,JWTAuth $JWTAuth, WeApp $wxxcx)
    {
        //code 在小程序端使用 wx.login 获取
        $code = request('code', '');
        //encryptedData 和 iv 在小程序端使用 wx.getUserInfo 获取
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');
        $oauthType = $request->input('oauthType',UserOauth::AUTH_TYPE_WEAPP);
        switch ($oauthType) {
            case UserOauth::AUTH_TYPE_WEAPP:
                //项目招募助手-默认
                break;
            case UserOauth::AUTH_TYPE_WEAPP_ASK:
                //企业点评服务
                $wxxcx->setConfig(config('weapp.appid_ask'),config('weapp.secret_ask'));
                break;
        }

        //根据 code 获取用户 session_key 等信息, 返回用户openid 和 session_key
        //ex:{"session_key":"sCKZIw/kW3Xy+3ykRmbLWQ==","expires_in":7200,"openid":"oW2D-0DjAQNvKiMqiDME5wpDdymE"}
        $userInfo = $wxxcx->getLoginInfo($code);

        \Log::info('userinfo',$userInfo);
        if(RateLimiter::instance()->increase('weapp:getUserInfo',$userInfo['openid'],2,1)){
            sleep(1);
        }

        //获取解密后的用户信息
        //ex:{\"openId\":\"oW2D-0DjAQNvKiMqiDME5wpDdymE\",\"nickName\":\"hank\",\"gender\":1,\"language\":\"zh_CN\",\"city\":\"Pudong New District\",\"province\":\"Shanghai\",\"country\":\"CN\",\"avatarUrl\":\"http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKibUNMkQ0sVd8jUPHGXia2G78608O9qs9eGAd06jeI2ZRHiaH4DbxI9ppsucxbemxuPawrBh95Sd3PA/0\",\"watermark\":{\"timestamp\":1497602544,\"appid\":\"wx5f163b8ab1c05647\"}}
        $return = $wxxcx->getUserInfo($encryptedData, $iv);

        $oauthData = UserOauth::where('auth_type',$oauthType)
            ->where('openid',$userInfo['openid'])->first();
        $user_id = 0;

        if (!$oauthData) {
            $unionId = null;
            if (isset($userInfo['unionid']) && $userInfo['unionid']) {
                $unionId = $userInfo['unionid'];
            } elseif (isset($return['unionId']) && $return['unionId']) {
                $unionId = $return['unionId'];
            }

            if ($unionId) {
                $oauthData = UserOauth::where('unionid',$unionId)->where('user_id','>',0)->first();
                if ($oauthData) {
                    $user_id = $oauthData->user_id;
                }
            }
            $oauthData = UserOauth::create(
                [
                    'auth_type'=>$oauthType,
                    'user_id'=> $user_id,
                    'openid'   => $userInfo['openid'],
                    'unionid'  => $unionId,
                    'nickname'=>$return['nickName']??'',
                    'avatar'=>$return['avatarUrl']??'',
                    'access_token'=>$userInfo['session_key'],
                    'refresh_token'=>'',
                    'expires_in'=>$userInfo['expires_in']??7200,
                    'full_info'=>$return,
                    'scope'=>'authorization_code',
                    'status' => 1
                ]
            );
        } else {
            $user_id = $oauthData->user_id;
        }

        $info = [
            'id' => $user_id,
            'oauth_id' => $oauthData->id,
            'status'=>$oauthData->status,
            'avatarUrl'=>$oauthData->avatar,
            'name'=>$oauthData->nickname,
            'company'=>'',
            'mobile' => '',
            'email'  => ''
        ];
        $token = $JWTAuth->fromUser($oauthData);
        Cache::set('weapp_session_key_'.$oauthData->id,$userInfo['session_key'],60*24*3);

        if ($oauthData->user_id) {
            $user = User::find($oauthData->user_id);
            $info['id'] = $user->id;
            $info['title'] = $user->title;
            $info['company'] = $user->company;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
        }
        event(new SystemNotify('用户登录: '.$oauthData->user_id.'['.$oauthData->nickname.'];设备:小程序登陆-'.$oauthType));
        return self::createJsonData(true,['token'=>$token,'userInfo'=>$info]);
    }

    public function updateUserInfo(Request $request,JWTAuth $JWTAuth) {
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($request->input('nickName')) {
            $oauth->update([
                'nickname' => $request->input('nickName'),
                'avatar' => $request->input('avatarUrl'),
                'full_info' => $request->all()
            ]);
        }
        $info = [
            'id' => $oauth->user_id,
            'oauth_id' => $oauth->id,
            'status'=>$oauth->status,
            'avatarUrl'=>$oauth->avatar,
            'name'=>$oauth->nickname,
            'company'=>'',
            'mobile' => '',
            'email'  => ''
        ];
        if ($oauth->user_id) {
            $user = User::find($oauth->user_id);
            $info['title'] = $user->title;
            $info['company'] = $user->company;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
        }
        event(new SystemNotify('用户完成微信认证: '.$oauth->user_id.'['.$oauth->nickname.'];设备:小程序'));
        return self::createJsonData(true,$info);
    }

    public function updatePhone(Request $request,JWTAuth $JWTAuth, WeApp $wxxcx) {
        $validateRules = [
            'encryptedData'   => 'required',
            'iv' => 'required'
        ];
        $this->validate($request,$validateRules);
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');
        switch ($request->input('inwehub_user_device')) {
            case 'weapp_dianping':
                $wxxcx->setConfig(config('weapp.appid_ask'),config('weapp.secret_ask'));
                break;
        }
        $oauth = $JWTAuth->parseToken()->toUser();
        $sessionKey = Cache::get('weapp_session_key_'.$oauth->id);
        $wxxcx->setSessionKey($sessionKey);
        $return = $wxxcx->getUserInfo($encryptedData, $iv);
        $phone = $return['purePhoneNumber'];
        $phoneUser = User::where('mobile',$phone)->first();
        if (!$oauth->user_id) {
            if ($phoneUser) {
                $oauth->user_id = $phoneUser->id;
                $oauth->save();
            } else {
                $registrar = new Registrar();
                $new_user = $registrar->create([
                    'name' => $oauth->nickname,
                    'email' => null,
                    'mobile' => $phone,
                    'rc_uid' => 0,
                    'title'  => '',
                    'company' => '',
                    'gender' => $oauth->full_info['gender']??0,
                    'password' => time(),
                    'visit_ip' => $request->getClientIp(),
                    'status' => 1,
                    'source' => User::USER_SOURCE_WEAPP_DB,
                ]);
                $oauth->user_id = $new_user->id;
                $oauth->save();
                $new_user->attachRole(2); //默认注册为普通用户角色
                $new_user->avatar = $oauth->avatar;
                $new_user->save();
                event(new UserRegistered($new_user,$oauth->id,'微信小程序-点评'));
            }
        } else {
            $user = User::find($oauth->user_id);
            if (empty($user->mobile)) {
                if ($phoneUser) {
                    $oauth->user_id = $phoneUser->id;
                    $oauth->save();
                } else {
                    $user->mobile = $phone;
                    $user->save();
                }
            }
        }
        $info = [
            'id' => $oauth->user_id,
            'oauth_id' => $oauth->id,
            'status'=>$oauth->status,
            'avatarUrl'=>$oauth->avatar,
            'name'=>$oauth->nickname,
            'company'=>'',
            'mobile' => $phone,
            'email'  => ''
        ];
        event(new SystemNotify('用户完成手机认证: '.$oauth->user_id.'['.$oauth->nickname.'];设备:小程序'));
        return self::createJsonData(true,$info);
    }

    public function getUserInfo(JWTAuth $JWTAuth){
        $total_unread = 0;
        $oauth = $JWTAuth->parseToken()->toUser();
        $status = $oauth->status;
        if ($oauth->user_id) {
            $user = $oauth->user;
            if ($oauth->auth_type == UserOauth::AUTH_TYPE_WEAPP) {
                $demand_ids = Demand::where('user_id',$oauth->user_id)->get()->pluck('id')->toArray();
                //获取未读消息数
                $im_rooms = Room::where('source_type',Demand::class)->where(function ($query) use ($user,$demand_ids) {$query->where('user_id',$user->id)->orWhereIn('source_id',$demand_ids);})->get();
                foreach ($im_rooms as $im_room) {
                    $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
                    $total_unread += $im_count;
                }
            }
            $info = [
                'id'=>$oauth->user_id,
                'oauth_id' => $oauth->id,
                'total_unread'=>$total_unread,
                'status'=>$status,
                'avatarUrl'=>$oauth->avatar,
                'title'=>$user->title,
                'company'=>$user->company,
                'name'=>$user->name,
                'mobile'=>$user->mobile,
                'email'=>$user->email
            ];
        } else {
            $info = [
                'id'=>0,
                'oauth_id' => $oauth->id,
                'total_unread'=>$total_unread,
                'status'=>$status,
                'avatarUrl'=>$oauth->avatar,
                'title'=>'',
                'company'=>'',
                'name'=>$oauth->nickname,
                'mobile'=>'',
                'email'=>''
            ];
        }
        return self::createJsonData(true,$info);
    }

    public function getQrCode(Request $request,WeApp $wxxcx){
        $validateRules = [
            'object_type'=> 'required|integer',
            'object_id'=> 'required|integer',

        ];
        $this->validate($request,$validateRules);
        $url = '';
        $cacheKey = '';
        switch ($request->input('object_type')) {
            case 1:
                //获取需求
                $object = Demand::findOrFail($request->input('object_id'));
                $cacheKey = 'demand-qrcode';
                $url = RateLimiter::instance()->hGet($cacheKey,$object->id);
                $page = 'pages/detail/detail';
                $scene = 'demand_id='.$object->id;
                break;
            default:
                throw new ApiException(ApiException::BAD_REQUEST);
                break;
        }
        try {
            if (!$url) {
                $qrcode = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
                $file_name = 'demand/qrcode/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.png';
                Storage::disk('oss')->put($file_name,$qrcode);
                $url = Storage::disk('oss')->url($file_name);
                RateLimiter::instance()->hSet($cacheKey,$object->id,$url);
            }
        } catch (\Exception $e) {
            return self::createJsonData(true,['qrcode'=>config('image.user_default_avatar')]);
        }
        return self::createJsonData(true,['qrcode'=>$url]);
    }

    public function getMessageRooms(JWTAuth $JWTAuth){
        $oauth = $JWTAuth->parseToken()->toUser();
        if ($oauth->user_id) {
            $user = $oauth->user;
        } else {
            $user = new \stdClass();
            $user->id = 0;
            $user->name = '游客';
        }
        $demand_ids = Demand::where('user_id',$user->id)->get()->pluck('id')->toArray();
        //获取未读消息数
        $im_rooms = Room::where('source_type',Demand::class)->where(function ($query) use ($user,$demand_ids) {$query->where('user_id',$user->id)->orWhereIn('source_id',$demand_ids);})->orderBy('id','desc')->paginate(Config::get('inwehub.api_data_page_size'));
        $im_list = [];
        foreach ($im_rooms as $im_room) {
            $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
            $last_message = MessageRoom::where('room_id',$im_room->id)->orderBy('id','desc')->first();
            $demand = Demand::find($im_room->source_id);
            $contact = User::find($im_room->user_id==$user->id?$demand->user_id:$im_room->user_id);
            $item = [
                'unread_count' => $im_count,
                'avatar'       => $contact->avatar,
                'name'         => $contact->name,
                'room_id'      => $im_room->id,
                'last_message' => [
                    'id' => $last_message?$last_message->message_id:0,
                    'text' => '',
                    'data'  => $last_message?$last_message->message->data:['text'=>'','img'=>''],
                    'read_at' => $last_message?$last_message->message->read_at:'',
                    'created_at' => $last_message?(string)$last_message->created_at:''
                ]
            ];
            $im_list[] = $item;
        }
        $return = $im_rooms->toArray();
        $return['data'] = $im_list;

        return self::createJsonData(true,$return);
    }

    public function saveFormId(Request $request,JWTAuth $JWTAuth) {
        $oauth = $JWTAuth->parseToken()->toUser();
        $formId = $request->input('formId');
        if ($formId) {
            RateLimiter::instance()->sAdd('user_oauth_formId_'.$oauth->id,$formId,60*60*24*6);
        }
        return self::createJsonData(true);
    }

}