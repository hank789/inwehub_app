<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/12/25 下午5:35
 * @email: wanghui@yonglibao.com
 */

use App\Models\IM\Conversation;
use App\Models\IM\Message;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use Illuminate\Console\Command;

class FixImData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:im';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复im数据';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $conversations = Conversation::orderBy('id','asc')->get();
        $rooms = [];
        $rooms2 = [];

        foreach ($conversations as $conversation) {
            $key = $conversation->user_id.'_'.$conversation->contact_id;
            if (isset($rooms[$key]) || in_array($key,$rooms)) {
                $room_id = $rooms2[$key]??$rooms2[$conversation->contact_id.'_'.$conversation->user_id];
                Message::where('id',$conversation->message_id)->update(['room_id'=>$room_id]);
            } else {
                $rooms[$key] = $conversation->contact_id.'_'.$conversation->user_id;
                $room = Room::create([
                    'user_id' => $conversation->user_id,
                    'r_type'  => 1,
                ]);
                $rooms2[$conversation->contact_id.'_'.$conversation->user_id] = $room->id;
                RoomUser::firstOrCreate([
                    'room_id' => $room->id,
                    'user_id' => $conversation->user_id
                ],[
                    'room_id' => $room->id,
                    'user_id' => $conversation->user_id
                ]);
                RoomUser::firstOrCreate([
                    'room_id' => $room->id,
                    'user_id' => $conversation->contact_id
                ],[
                    'room_id' => $room->id,
                    'user_id' => $conversation->contact_id
                ]);
                Message::where('id',$conversation->message_id)->update(['room_id'=>$room->id]);
            }
        }
    }

}