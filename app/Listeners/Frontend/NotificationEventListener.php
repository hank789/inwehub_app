<?php

namespace App\Listeners\Frontend;
use App\Events\Frontend\Notification\MarkAsRead;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;


/**
 * Class UserEventListener.
 */
class NotificationEventListener implements ShouldQueue
{

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    /**
     * 标记通知为已读
     * @param MarkAsRead $event
     */
    public function markAsRead($event){
        $user = User::find($event->user_id);
        $query = Notification::where('notifiable_id',$user->id)->where('notifiable_type',get_class($user))->whereNull('read_at');
        if ($event->notification_type) {
            $query = $query->where('notification_type',$event->notification_type);
        } else {
            //全部已读
            $im_rooms = Room::where('source_type',User::class)->where(function ($query) use ($user) {$query->where('user_id',$user->id)->orWhere('source_id',$user->id);})->get();
            foreach ($im_rooms as $im_room) {
                $last_msg_id = MessageRoom::where('room_id',$im_room->id)->max('message_id');
                $roomUser = RoomUser::firstOrCreate([
                    'user_id' => $user->id,
                    'room_id' => $im_room->id
                ],[
                    'user_id' => $user->id,
                    'room_id' => $im_room->id
                ]);
                $roomUser->last_msg_id = $last_msg_id;
                $roomUser->save();
            }
            //用户群聊
            $groupMembers = GroupMember::where('user_id',$user->id)
                ->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->get();
            foreach ($groupMembers as $groupMember) {
                $group = Group::find($groupMember->group_id);
                $room = Room::where('r_type',Room::ROOM_TYPE_GROUP)
                    ->where('source_id',$group->id)
                    ->where('source_type',get_class($group))
                    ->where('status',Room::STATUS_OPEN)->first();
                if ($room) {
                    $last_msg_id = MessageRoom::where('room_id',$room->id)->max('message_id');
                    $roomUser = RoomUser::where('user_id',$user->id)->where('room_id',$room->id)->first();
                    if ($roomUser) {
                        $roomUser->last_msg_id = $last_msg_id;
                        $roomUser->save();
                    }
                }
            }
        }
        $query->update(['read_at' => Carbon::now()]);
        Cache::delete('user_notification_count_'.$user->id);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            MarkAsRead::class,
            'App\Listeners\Frontend\NotificationEventListener@markAsRead'
        );
    }
}
