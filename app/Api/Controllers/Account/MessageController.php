<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\IM\Message;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Notifications\NewMessage;
use App\Services\RateLimiter;
use App\Services\Registrar;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Monolog\Handler\IFTTTHandler;
use Tymon\JWTAuth\JWTAuth;

class MessageController extends Controller
{
    public function getMessages(Request $request,JWTAuth $JWTAuth)
    {
        $this->validate($request, [
            'room_id' => 'required|integer|min:1',
            'page'       => 'required|integer',
        ]);
        $room_id = $request->input('room_id');
        $room = Room::findOrFail($room_id);
        switch ($room->source_type) {
            case User::class:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                break;
            case Demand::class:
                $payload = $JWTAuth->parseToken()->getPayload();
                $oauthUser = UserOauth::find($payload['sub']);
                if (!$oauthUser) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                $user = $oauthUser->user;
                break;
            case Group::class:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                    $groupMember = GroupMember::where('group_id',$room->source_id)->where('user_id',$user->id)
                        ->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                if (!$groupMember) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
                break;
        }

        $messages = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $room_id)
            ->select('im_messages.*')
            ->orderBy('im_messages.id', 'desc')
            ->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();

        $roomUser = RoomUser::where('room_id',$room_id)->where('user_id','!=',$user->id)->first();
        $users = [];
        $users[$user->id] = ['avatar'=>$user->avatar,'uuid'=>$user->uuid,'name'=>$user->name];
        if ($roomUser) $users[$roomUser->user->id] = ['avatar'=>$roomUser->user->avatar,'uuid'=>$roomUser->user->uuid,'name'=>$roomUser->user->name];
        if ($messages['data']) {
            foreach ($messages['data'] as &$item) {
                if (!isset($users[$item['user_id']])) {
                    $contact = User::find($item['user_id']);
                    $users[$contact->id] = ['avatar'=>$contact->avatar,'uuid'=>$contact->uuid,'name'=>$contact->name];
                }
                $item['avatar'] = $users[$item['user_id']]['avatar'];
                $item['uuid'] = $users[$item['user_id']]['uuid'];
                $item['user_name'] = $users[$item['user_id']]['name'];
                $item['data'] = json_decode($item['data'],true);
                $item['created_at_timestamp'] = strtotime($item['created_at']);
            }
            $messages['data'] = array_reverse($messages['data']);
        }
        $messages['contact'] = [];
        if ($roomUser) {
            $messages['contact'] = [
                'name' => $roomUser->user->name,
                'id'   => $roomUser->user->id
            ];
        }
        $messages['room_id'] = $room_id;
        return self::createJsonData(true,$messages);
    }


    public function store(Request $request,JWTAuth $JWTAuth)
    {
        $this->validate($request, [
            'text'    => 'required_without:img',
            'img'    => 'required_without:text',
            'room_id' => 'required|integer|min:1',
            'contact_id' => 'required|integer',
        ]);

        $room_id = $request->input('room_id');
        $room = Room::findOrFail($room_id);
        if ($room->status != Room::STATUS_OPEN) {
            throw new ApiException(ApiException::IM_ROOM_CLOSED);
        }
        switch ($room->source_type) {
            case User::class:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                break;
            case Demand::class:
                $payload = $JWTAuth->parseToken()->getPayload();
                $oauthUser = UserOauth::find($payload['sub']);
                if (!$oauthUser) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                $user = $oauthUser->user;
                if ($request->input('formId')) {
                    RateLimiter::instance()->sAdd('user_oauth_formId_'.$oauthUser->id,$request->input('formId'),60*60*24*6);
                }
                break;
            case Group::class:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                    $groupMember = GroupMember::where('group_id',$room->source_id)->where('user_id',$user->id)
                        ->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->first();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                if (!$groupMember) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
                break;
        }
        $contact_id = $request->input('contact_id');

        $base64Img = $request->input('img');
        $data = [];
        $data['text'] = $request->input('text');
        if ($base64Img) {
            if ($base64Img == 1) {
                //小程序上传
                if($request->hasFile('img_file')){
                    $file_0 = $request->file('img_file');
                    $extension = strtolower($file_0->getClientOriginalExtension());
                    $file_name = 'message/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$extension;
                    dispatch((new UploadFile($file_name,base64_encode(File::get($file_0)))));
                    $data['img'] = Storage::disk('oss')->url($file_name);
                }
            } else {
                $url = explode(';',$base64Img);
                $url_type = explode('/',$url[0]);
                $file_name = 'message/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
                dispatch((new UploadFile($file_name,(substr($url[1],6)))));
                $data['img'] = Storage::disk('oss')->url($file_name);
            }
        }
        $message = $user->messages()->create([
            'data' => $data,
        ]);

        $roomUser = RoomUser::firstOrCreate([
            'user_id' => $user->id,
            'room_id' => $room_id
        ],[
            'user_id' => $user->id,
            'room_id' => $room_id
        ]);

        MessageRoom::create([
            'room_id' => $room_id,
            'message_id' => $message->id
        ]);
        $roomUser->last_msg_id = $message->id;
        $roomUser->save();

        if ($contact_id) {
            RoomUser::firstOrCreate([
                'user_id' => $contact_id,
                'room_id' => $room_id
            ],[
                'user_id' => $contact_id,
                'room_id' => $room_id
            ]);
            // broadcast the message to the other person
            $contact = User::find($contact_id);
            $contact->notify(new NewMessage($contact_id,$message,$room_id));
        } elseif ($room->source_type == Group::class) {
            $members = GroupMember::where('group_id',$room->source_id)
                ->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->get();
            foreach ($members as $member) {
                if ($member->user_id == $user->id) continue;
                $notifyUser = $member->user;
                $notifyUser->to_slack = false;
                $notifyUser->to_push = (RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('user_chat',$room->id.'-'.$member->user_id,30));
                $notifyUser->notify(new NewMessage($member->user_id,$message,$room_id));

            }
            $group = Group::find($room->source_id);
            $fields = [];
            if (isset($message->data['text']) && $message->data['text']) {
                $fields[] = [
                    'title' => '回复内容',
                    'value' => $message->data['text']
                ];
            }
            if (isset($message->data['img']) && $message->data['img']) {
                $fields[] = [
                    'title' => '回复图片',
                    'value' => $message->data['img']
                ];
            }
            event(new SystemNotify('用户'.$user->id.'['.$user->name.']回复了圈子['.$group->name.']',$fields));
        }
        $return = $message->toArray();
        $return['avatar'] = $user->avatar;
        return self::createJsonData(true, $return);
    }

    public function getWhisperRoom(Request $request) {
        $this->validate($request, [
            'contact_id' => 'required|integer|min:1'
        ]);
        $user = $request->user();
        $contact_id = $request->input('contact_id');
        $contact = User::find($contact_id);
        //私信
        $room = Room::where('user_id',$user->id)
            ->where('source_id',$contact_id)
            ->where('source_type',User::class)
            ->first();
        if (!$room) {
            $room = Room::where('user_id',$contact_id)
                ->where('source_id',$user->id)
                ->where('source_type',User::class)
                ->first();
        }
        if ($room) {
            $room_id = $room->id;
        } else {
            $room = Room::create([
                'user_id' => $user->id,
                'source_id' => $contact_id,
                'source_type' => get_class($user),
                'r_name' => '私信',
                'r_type'  => Room::ROOM_TYPE_WHISPER
            ]);
            $room_id = $room->id;
            RoomUser::firstOrCreate([
                'user_id' => $user->id,
                'room_id' => $room_id
            ],[
                'user_id' => $user->id,
                'room_id' => $room_id
            ]);

            RoomUser::firstOrCreate([
                'user_id' => $contact_id,
                'room_id' => $room_id
            ],[
                'user_id' => $contact_id,
                'room_id' => $room_id
            ]);
        }
        $last_msg_id = MessageRoom::where('room_id',$room->id)->max('message_id');
        $roomUser = RoomUser::firstOrCreate([
            'user_id' => $user->id,
            'room_id' => $room->id
        ],[
            'user_id' => $user->id,
            'room_id' => $room->id
        ]);
        $roomUser->last_msg_id = $last_msg_id;
        $roomUser->save();
        return self::createJsonData(true,['room_id'=>$room_id,'contact_id'=>$contact_id,'contact_name'=>$contact->name]);
    }

    public function getRoom(Request $request,JWTAuth $JWTAuth){
        $this->validate($request, [
            'room_id' => 'required|integer|min:1'
        ]);
        $room = Room::findOrFail($request->input('room_id'));
        $return = $room->toArray();
        switch ($return['source_type']) {
            case User::class:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                $source = User::find($room->source_id);
                if ($room->source_id == $user->id) {
                    $contact = [
                        'id' => $room->user_id,
                        'name'=>$room->user->name
                    ];
                } else {
                    $contact = [
                        'id' => $room->source_id,
                        'name'=>$source->name
                    ];
                }
                break;
            case Demand::class:
                $payload = $JWTAuth->parseToken()->getPayload();
                $oauthUser = UserOauth::find($payload['sub']);
                if (!$oauthUser) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                $user = $oauthUser->user;
                $demand = Demand::find($room->source_id);
                $oauth = $demand->user->userOauth->where('auth_type',UserOauth::AUTH_TYPE_WEAPP)->first();
                $source = [
                    'publisher_name'=>$oauth->nickname,
                    'publisher_avatar'=>$oauth->avatar,
                    'publisher_title'=>$demand->user->title,
                    'publisher_company'=>$demand->user->company,
                    'publisher_email'=>$demand->user->email,
                    'publisher_phone' => $demand->user->mobile,
                    'title' => $demand->title,
                    'address' => $demand->address,
                    'salary' => salaryFormat($demand->salary),
                    'salary_upper' => salaryFormat($demand->salary_upper),
                    'salary_type' => $demand->salary_type,
                    'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
                    'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
                    'project_begin_time' => $demand->project_begin_time,
                    'description' => $demand->description,
                ];
                if ($room->user_id == $user->id) {
                    $contact = [
                        'id' => $oauth->user_id,
                        'name'=>$oauth->nickname
                    ];
                } else {
                    $contact = [
                        'id' => $room->user_id,
                        'name'=>$room->user->name
                    ];
                }
                break;
            case Group::class:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                $source = Group::find($room->source_id);
                $contact = [];
                //标记该用户已读圈子内聊天信息
                RateLimiter::instance()->sAdd('group_im_users:'.$room->id,$user->id,0);
                break;
        }
        $return['contact'] = $contact;
        $return['source'] = $source;
        $last_msg_id = MessageRoom::where('room_id',$room->id)->max('message_id');
        $roomUser = RoomUser::firstOrCreate([
            'user_id' => $user->id,
            'room_id' => $room->id
        ],[
            'user_id' => $user->id,
            'room_id' => $room->id
        ]);
        $roomUser->last_msg_id = $last_msg_id;
        $roomUser->save();
        return self::createJsonData(true,$return);
    }

    public function createRoom(Request $request,JWTAuth $JWTAuth){
        $this->validate($request, [
            'source_type' => 'required|in:1,2',
            'source_id' => 'required|integer|min:1',
            'contact_id' => 'required|integer|min:1'
        ]);
        switch ($request->input('source_type')){
            case 1:
                try {
                    $user = $JWTAuth->parseToken()->authenticate();
                } catch (\Exception $e) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                //纯私信
                $room = Room::where('user_id',$user->id)
                    ->where('source_id',$request->input('source_id'))
                    ->where('source_type',User::class)
                    ->first();
                if (!$room) {
                    $room = Room::where('user_id',$request->input('source_id'))
                        ->where('source_id',$user->id)
                        ->where('source_type',User::class)
                        ->first();
                    if (!$room) {
                        $room = Room::create([
                            'user_id' => $user->id,
                            'source_id' => $request->input('source_id'),
                            'source_type' => get_class($user),
                            'r_name' => '私信',
                            'r_type'  => Room::ROOM_TYPE_WHISPER
                        ]);
                        RoomUser::firstOrCreate([
                            'user_id' => $user->id,
                            'room_id' => $room->id
                        ],[
                            'user_id' => $user->id,
                            'room_id' => $room->id
                        ]);

                        RoomUser::firstOrCreate([
                            'user_id' => $request->input('source_id'),
                            'room_id' => $room->id
                        ],[
                            'user_id' => $request->input('source_id'),
                            'room_id' => $room->id
                        ]);
                    }
                }
                break;
            case 2:
                $payload = $JWTAuth->parseToken()->getPayload();
                $oauthUser = UserOauth::find($payload['sub']);
                if (!$oauthUser) {
                    throw new ApiException(ApiException::TOKEN_INVALID);
                }
                $user = $oauthUser->user;
                if (!$user) {
                    $registrar = new Registrar();
                    $user = $registrar->create([
                        'name' => $oauthUser->nickname,
                        'email' => null,
                        'mobile' => null,
                        'rc_uid' => 0,
                        'title'  => '',
                        'company' => '',
                        'gender' => $oauthUser['full_info']['gender'],
                        'password' => time(),
                        'status' => 1,
                        'source' => User::USER_SOURCE_WEAPP,
                        'visit_ip' => $request->getClientIp()
                    ]);
                    $oauthUser->user_id = $user->id;
                    $oauthUser->save();
                    $user->attachRole(2); //默认注册为普通用户角色
                    $user->avatar = $oauthUser->avatar;
                    $user->save();
                }
                //小程序需求发布
                $demand = Demand::find($request->input('source_id'));
                if (!$demand) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
                $room = Room::where('user_id',$user->id)
                    ->where('source_id',$request->input('source_id'))
                    ->where('source_type',Demand::class)
                    ->first();
                if (!$room) {
                    $room = Room::where('user_id',$request->input('contact_id'))
                        ->where('source_id',$user->id)
                        ->where('source_type',Demand::class)
                        ->first();
                    if (!$room) {
                        $room = Room::create([
                            'user_id' => $demand->user_id == $user->id?$request->input('contact_id'):$user->id,
                            'source_id' => $request->input('source_id'),
                            'source_type' => get_class($demand),
                            'r_name' => $demand->title,
                            'r_description' => $demand->title,
                            'r_type'  => Room::ROOM_TYPE_WHISPER
                        ]);
                        RoomUser::firstOrCreate([
                            'user_id' => $user->id,
                            'room_id' => $room->id
                        ],[
                            'user_id' => $user->id,
                            'room_id' => $room->id
                        ]);

                        RoomUser::firstOrCreate([
                            'user_id' => $demand->user_id,
                            'room_id' => $room->id
                        ],[
                            'user_id' => $demand->user_id,
                            'room_id' => $room->id
                        ]);
                    }
                }
                break;
        }
        return self::createJsonData(true,['id'=>$room->id]);

    }


    /**
     * marks all conversation's messages as read.
     *
     * @param int $contact_id
     *
     * @return void
     */
    protected function markAllAsRead($contact_id)
    {
        Auth::user()->conversations()->where('contact_id', $contact_id)->get()->map(function ($m) {
            if (Auth::user()->id != $m->user_id) $m->update(['read_at' => Carbon::now()]);
        });
    }
}
