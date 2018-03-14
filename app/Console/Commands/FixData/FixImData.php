<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/12/25 下午5:35
 * @email: wanghui@yonglibao.com
 */

use App\Models\IM\Conversation;
use App\Models\IM\Message;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\User;
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
        $rooms = Room::get();
        foreach ($rooms as $room) {
            try {
                $roomUser = RoomUser::where('room_id',$room->id)
                    ->where('user_id','!=',$room->user_id)
                    ->first();
                $room->source_id = $roomUser->user_id;
                $room->source_type = User::class;
                $room->r_name = '私信';
                $room->save();
            } catch (\Exception $e) {
                var_dump($room->id);
            }
        }
    }

}