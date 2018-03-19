<?php namespace App\Api\Controllers\Weapp;
/**
 * @author: wanghui
 * @date: 2017/6/16 下午2:00
 * @email: wanghui@yonglibao.com
 */
use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
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
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\JWTAuth;

class UserController extends controller {

    //小程序登录获取用户信息
    public function getWxUserInfo(Request $request,JWTAuth $JWTAuth,Registrar $registrar, WeApp $wxxcx)
    {
        //code 在小程序端使用 wx.login 获取
        $code = request('code', '');
        //encryptedData 和 iv 在小程序端使用 wx.getUserInfo 获取
        $encryptedData = request('encryptedData', '');
        $iv = request('iv', '');

        //根据 code 获取用户 session_key 等信息, 返回用户openid 和 session_key
        //ex:{"session_key":"sCKZIw/kW3Xy+3ykRmbLWQ==","expires_in":7200,"openid":"oW2D-0DjAQNvKiMqiDME5wpDdymE"}
        $userInfo = $wxxcx->getLoginInfo($code);

        //获取解密后的用户信息
        //ex:{\"openId\":\"oW2D-0DjAQNvKiMqiDME5wpDdymE\",\"nickName\":\"hank\",\"gender\":1,\"language\":\"zh_CN\",\"city\":\"Pudong New District\",\"province\":\"Shanghai\",\"country\":\"CN\",\"avatarUrl\":\"http://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKibUNMkQ0sVd8jUPHGXia2G78608O9qs9eGAd06jeI2ZRHiaH4DbxI9ppsucxbemxuPawrBh95Sd3PA/0\",\"watermark\":{\"timestamp\":1497602544,\"appid\":\"wx5f163b8ab1c05647\"}}
        $return = $wxxcx->getUserInfo($encryptedData, $iv);

        \Log::info('return',$return);
        $token = '';
        $oauthData = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEAPP)
            ->where('openid',$userInfo['openid'])->first();
        $user_id = 0;

        if (!$oauthData) {
            if (isset($return['unionId'])) {
                $oauthData = UserOauth::whereIn('auth_type',[UserOauth::AUTH_TYPE_WEIXIN,UserOauth::AUTH_TYPE_WEIXIN_GZH])
                    ->where('unionid',$return['unionId'])->first();
                if ($oauthData) {
                    $user_id = $oauthData->user_id;
                }
            }
            $oauthData = UserOauth::create(
                [
                    'auth_type'=>UserOauth::AUTH_TYPE_WEAPP,
                    'user_id'=> $user_id,
                    'openid'   => $userInfo['openid'],
                    'unionid'  => $return['unionId']??null,
                    'nickname'=>$return['nickName'],
                    'avatar'=>$return['avatarUrl'],
                    'access_token'=>$userInfo['session_key'],
                    'refresh_token'=>'',
                    'expires_in'=>$userInfo['expires_in'],
                    'full_info'=>json_encode($return),
                    'scope'=>'authorization_code',
                    'status' => 0
                ]
            );
        } else {
            $user_id = $oauthData->user_id;
        }
        $info = [
            'id' => $user_id,
            'status'=>$oauthData->status,
            'avatarUrl'=>$oauthData->avatar,
            'name'=>$oauthData->nickname,
            'company'=>'',
            'mobile' => '',
            'email'  => ''
        ];

        if ($oauthData && $oauthData->user_id) {
            $user = User::find($oauthData->user_id);
            $token = $JWTAuth->fromUser($user);
            $info['id'] = $user->id;
            $info['title'] = $user->title;
            $info['company'] = $user->company;
            $info['mobile'] = $user->mobile;
            $info['email'] = $user->email;
            event(new UserLoggedIn($user,'小程序登陆'));
        }

        return self::createJsonData(true,['token'=>$token,'userInfo'=>$info,'openid'=>$userInfo['openid']]);
    }

    public function getUserInfo(Request $request,JWTAuth $JWTAuth){
        $total_unread = 0;
        try {
            $user = $JWTAuth->parseToken()->authenticate();
            $oauth = $user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
            $status = $oauth->status;
            $demand_ids = Demand::where('user_id',$user->id)->get()->pluck('id')->toArray();
            //获取未读消息数
            $im_rooms = Room::where('source_type',Demand::class)->where(function ($query) use ($user,$demand_ids) {$query->where('user_id',$user->id)->orWhereIn('source_id',$demand_ids);})->get();
            foreach ($im_rooms as $im_room) {
                $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
                $total_unread += $im_count;
            }
        } catch (\Exception $e) {
            $oauth = UserOauth::where('auth_type',UserOauth::AUTH_TYPE_WEAPP)
                ->where('openid',$request->input('openid'))->first();
            $user = new \stdClass();
            $status = 0;
            $user->id = 0;
            $user->mobile = '';
            $user->email = '';
            $user->title = '';
            $user->company = '';
            $user->name = $oauth->nickname;
        }
        return self::createJsonData(true,['id'=>$user->id,'total_unread'=>$total_unread,'status'=>$status,'avatarUrl'=>$oauth->avatar,'title'=>$user->title,'company'=>$user->company,'name'=>$user->name,'mobile'=>$user->mobile,'email'=>$user->email]);
    }

    public function getQrCode(Request $request,WeApp $wxxcx){
        $validateRules = [
            'object_type'=> 'required|integer',
            'object_id'=> 'required|integer',

        ];
        $this->validate($request,$validateRules);
        switch ($request->input('object_type')) {
            case 1:
                //获取需求
                $demand = Demand::findOrFail($request->input('object_id'));
                $page = 'pages/detail/detail';
                $scene = 'demand_id='.$demand->id;
                break;
        }
        try {
            $res_array = $wxxcx->getQRCode()->getQRCodeB($scene,$page);
        } Catch (\Exception $e) {
            return self::createJsonData(true,['qrcode'=>config('image.user_default_avatar')]);
        }
        return self::createJsonData(true,['qrcode'=>$res_array]);
    }

    public function getMessageRooms(Request $request){
        $user = $request->user();
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
                'contact_id'   => $contact->id,
                'contact_uuid' => $contact->uuid,
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

}