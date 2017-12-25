<?php namespace App\Api\Controllers\Account;

use App\Api\Controllers\Controller;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Exceptions\ApiException;
use App\Jobs\UploadFile;
use App\Models\IM\Message;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function getMessages(Request $request)
    {
        $this->validate($request, [
            'room_id' => 'required|integer',
            'contact_id' => 'required|integer',
            'page'       => 'required|integer',
        ]);

        $user = $request->user();
        $room_id = $request->input('room_id');
        $contact_id = $request->input('contact_id');
        if ($room_id <= 0 && $contact_id) {
            //私信
            $room_ids = RoomUser::select('room_id')->where('user_id',$user->id)->get()->pluck('room_id')->toArray();
            $roomUser = RoomUser::select('room_id')->where('user_id',$contact_id)->whereIn('room_id',$room_ids)->first();
            $room_id = $roomUser->room_id;
        }

        $messages = $user->messages()
            ->where('room_id', $room_id)
            ->orderBy('id', 'asc')
            ->simplePaginate(Config::get('api_data_page_size'))->toArray();

        Message::where('room_id',$room_id)->where('user_id','!=',$user->id)->update(['read_at' => Carbon::now()]);

        $users = [];
        $users[$user->id] = ['avatar'=>$user->avatar,'uuid'=>$user->uuid];
        $contact = User::find($contact_id);
        $users[$contact->id] = ['avatar'=>$contact->avatar,'uuid'=>$contact->uuid];

        foreach ($messages['data'] as &$item) {
            if (!isset($users[$item['user_id']])) {
                $contact = User::find($item['user_id']);
                $users[$contact->id] = ['avatar'=>$contact->avatar,'uuid'=>$contact->uuid];
            }
            $item['avatar'] = $users[$item['user_id']]['avatar'];
            $item['uuid'] = $users[$item['user_id']]['uuid'];
        }
        $messages['contact'] = [
            'name' => $contact->name,
            'id'   => $contact->id
        ];
        $messages['room_id'] = $room_id;
        return self::createJsonData(true,$messages);
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'text'    => 'required_without:img',
            'img'    => 'required_without:text',
            'room_id' => 'required|integer',
            'contact_id' => 'required|integer',
        ]);

        $room_id = $request->input('room_id');
        $user =  Auth::user();

        if ($room_id <= 0) {
            $room = Room::create([
                'user_id' => $user->id,
                'r_type'  => Room::ROOM_TYPE_WHISPER
            ]);
            $room_id = $room->id;
        }
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
            'room_id' => $room_id
        ]);

        RoomUser::firstOrCreate([
            'user_id' => $user->id,
            'room_id' => $room_id
        ],[
            'user_id' => $user->id,
            'room_id' => $room_id
        ]);

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
            $contact->notify(new NewMessage($contact_id,$message));
        }


        return self::createJsonData(true, $message->toArray());
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
