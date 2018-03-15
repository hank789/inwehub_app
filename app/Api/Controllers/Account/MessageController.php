<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\IM\Message;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\User;
use App\Models\UserOauth;
use App\Models\Weapp\Demand;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Monolog\Handler\IFTTTHandler;

class MessageController extends Controller
{
    public function getMessages(Request $request)
    {
        $this->validate($request, [
            'room_id' => 'required|integer|min:1',
            'page'       => 'required|integer',
        ]);

        $user = $request->user();
        $room_id = $request->input('room_id');

        $messages = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $room_id)
            ->select('im_messages.*')
            ->orderBy('im_messages.id', 'desc')
            ->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();

        if ($messages['data']) {
            Message::where('user_id','!=',$user->id)->whereIn('id',array_column($messages['data'],'id'))->update(['read_at' => Carbon::now()]);
        }
        $roomUser = RoomUser::where('room_id',$room_id)->where('user_id','!=',$user->id)->first();
        $users = [];
        $users[$user->id] = ['avatar'=>$user->avatar,'uuid'=>$user->uuid];
        $users[$roomUser->user->id] = ['avatar'=>$roomUser->user->avatar,'uuid'=>$roomUser->user->uuid];
        if ($messages['data']) {
            foreach ($messages['data'] as &$item) {
                if (!isset($users[$item['user_id']])) {
                    $contact = User::find($item['user_id']);
                    $users[$contact->id] = ['avatar'=>$contact->avatar,'uuid'=>$contact->uuid];
                }
                $item['avatar'] = $users[$item['user_id']]['avatar'];
                $item['uuid'] = $users[$item['user_id']]['uuid'];
                $item['data'] = json_decode($item['data'],true);
            }
            $messages['data'] = array_reverse($messages['data']);
        }

        $messages['contact'] = [
            'name' => $roomUser->user->name,
            'id'   => $roomUser->user->id
        ];
        $messages['room_id'] = $room_id;
        return self::createJsonData(true,$messages);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'text'    => 'required_without:img',
            'img'    => 'required_without:text',
            'room_id' => 'required|integer|min:1',
            'contact_id' => 'required|integer|min:1',
        ]);

        $room_id = $request->input('room_id');
        $user =  Auth::user();
        $contact_id = $request->input('contact_id');

        $base64Img = $request->input('img');
        $data = [];
        $data['text'] = $request->input('text');
        if ($base64Img) {
            $url = explode(';',$base64Img);
            $url_type = explode('/',$url[0]);
            $file_name = 'message/'.date('Y').'/'.date('m').'/'.time().str_random(7).'.'.$url_type[1];
            dispatch((new UploadFile($file_name,(substr($url[1],6)))));
            $data['img'] = Storage::disk('oss')->url($file_name);
        }
        $message = $user->messages()->create([
            'data' => $data,
        ]);

        RoomUser::firstOrCreate([
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
        return self::createJsonData(true,['room_id'=>$room_id,'contact_id'=>$contact_id]);
    }

    public function getRoom(Request $request){
        $this->validate($request, [
            'source_type' => 'required|in:1,2',
            'source_id' => 'required|integer|min:1'
        ]);
        $user = $request->user();
        switch ($request->input('source_type')){
            case 1:
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
                $contact_id = $request->input('source_id');
                $source = User::find($contact_id)->toArray();
                break;
            case 2:
                //小程序需求发布
                $demand = Demand::find($request->input('source_id'));
                if (!$demand) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
                if ($user->id == $demand->user_id) {
                    throw new ApiException(ApiException::BAD_REQUEST);
                }
                $room = Room::where('user_id',$user->id)
                    ->where('source_id',$request->input('source_id'))
                    ->where('source_type',Demand::class)
                    ->first();
                if (!$room) {
                    $room = Room::create([
                        'user_id' => $user->id,
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
                $contact_id = $demand->user_id;
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
                    'salary' => $demand->salary,
                    'industry' => ['value'=>$demand->industry,'text'=>$demand->getIndustryName()],
                    'project_cycle' => ['value'=>$demand->project_cycle,'text'=>trans_project_project_cycle($demand->project_cycle)],
                    'project_begin_time' => $demand->project_begin_time,
                    'description' => $demand->description,
                ];
                break;
        }
        $return = $room->toArray();
        $return['contact_id'] = $contact_id;
        $return['source'] = $source;
        return self::createJsonData(true,$return);

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
