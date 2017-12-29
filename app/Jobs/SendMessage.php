<?php

namespace App\Jobs;

use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $from_uid;
    public $to_uids;
    public $message;



    public function __construct($message, $from_uid, $to_uids = [])
    {
        $this->message = $message;
        $this->from_uid = $from_uid;
        $this->to_uids = $to_uids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from_user = User::find($this->from_uid);
        $data = [];
        $data['text'] = $this->message;
        $message = $from_user->messages()->create([
            'data' => $data,
        ]);

        $contact_ids = $this->to_uids;
        if (empty($this->to_uids)) {
            $contact_ids = User::where('id','!=',$this->from_uid)->select('id')->get()->pluck('id')->toArray();
        }

        foreach ($contact_ids as $contact_id) {
            $room_ids = RoomUser::select('room_id')->where('user_id',$from_user->id)->get()->pluck('room_id')->toArray();
            $roomUser = RoomUser::where('user_id',$contact_id)->whereIn('room_id',$room_ids)->first();
            if ($roomUser) {
                $room_id = $roomUser->room_id;
            } else {
                $room = Room::create([
                    'user_id' => $from_user->id,
                    'r_type'  => Room::ROOM_TYPE_WHISPER
                ]);
                $room_id = $room->id;
            }

            RoomUser::firstOrCreate([
                'user_id' => $from_user->id,
                'room_id' => $room_id
            ],[
                'user_id' => $from_user->id,
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

            $to_user = User::find($contact_id);
            // broadcast the message to the other person
            $to_user->to_slack = false;
            $to_user->notify(new NewMessage($contact_id,$message));
        }
    }
}
