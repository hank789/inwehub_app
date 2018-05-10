<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Logic\TaskLogic;
use App\Models\IM\Conversation;
use App\Models\IM\Message;
use App\Models\IM\MessageRoom;
use App\Models\IM\Room;
use App\Models\IM\RoomUser;
use App\Models\Notification;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Services\NotificationSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NotificationController extends Controller
{

    protected $notificationSettings = [
        'push_system_notify'=>1,
        'push_rel_mine_upvoted'=>1,
        'push_rel_mine_followed'=>1,
        'push_rel_mine_mentioned'=>1,
        'push_rel_mine_commented'=>1,
        'push_rel_mine_invited'=>1,
        'push_rel_mine_chatted'=>1,
        'push_my_user_new_activity'=>1,
        'push_my_question_new_answered'=>1,
        'push_do_not_disturb' => 0
    ];

    public function readhubList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }

    public function taskList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }

    public function noticeList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }

    public function moneyList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }


    public function markAsRead(Request $request)
    {
        $notification_type = $request->input('notification_type',0);
        $user = $request->user();
        $query = $user->unreadNotifications();
        if ($notification_type) {
            $query = $query->where('notification_type',$notification_type);
        } else {
            //全部已读
            $im_rooms = Room::where('source_type',User::class)->where(function ($query) use ($user) {$query->where('user_id',$user->id)->orWhere('source_id',$user->id);})->get();
            foreach ($im_rooms as $im_room) {
                $unreads = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->select('im_messages.id')->get()->pluck('id')->toArray();
                if ($unreads) {
                    Message::whereIn('id',$unreads)->update(['read_at' => Carbon::now()]);
                }
            }
        }
        $query->update(['read_at' => Carbon::now()]);
        return self::createJsonData(true);
    }

    public function count(Request $request){
        $user = $request->user();
        $todo_task = $user->tasks()->where('status',0)->count();
        $notice_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->count();
        $task_notice_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->count();
        $readhub_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->count();
        $money_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->count();

        $total_unread =  $notice_unread_count + $task_notice_unread_count + $readhub_unread_count + $money_unread_count + $todo_task;
        $im_rooms = Room::where('source_type',User::class)->where(function ($query) use ($user) {$query->where('user_id',$user->id)->orWhere('source_id',$user->id);})->get();

        $im_list = [];
        $is_kefu_in = false;
        //客服
        $role = Role::customerService()->first();
        $role_user = RoleUser::where('role_id',$role->id)->first();
        if (!$role_user) {
            throw new ApiException(ApiException::ERROR);
        }
        $customer_id = $role_user->user_id;
        $customer_user = User::find($customer_id);
        $customer_message = [];

        foreach ($im_rooms as $im_room) {
            $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room->id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
            $total_unread += $im_count;
            $last_message = MessageRoom::where('room_id',$im_room->id)->orderBy('id','desc')->first();
            $contact = User::find($im_room->user_id==$user->id?$im_room->source_id:$im_room->user_id);
            if (!$contact) continue;
            $created_at = $last_message?(string)$last_message->created_at:'';
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
                    'created_time' => $created_at,
                    'created_at' => timestamp_format($created_at)
                ]
            ];
            if ($contact->id == $customer_id) {
                $is_kefu_in = true;
                $customer_message = $item;
            } else {
                $im_list[] = $item;
            }
        }
        if ($is_kefu_in == false) {
            //把客服小哈加进去
            $customer_message = [
                'unread_count' => 0,
                'avatar'       => $customer_user->avatar,
                'name'         => $customer_user->name,
                'room_id'      => 0,
                'contact_id'   => $customer_user->id,
                'contact_uuid' => $customer_user->uuid,
                'last_message' => [
                    'id' => 0,
                    'text' => '',
                    'data' => ['text'=>'您好，欢迎来到InweHub！','img'=>''],
                    'read_at' => '',
                    'created_at' => ''
                ]
            ];
        }
        usort($im_list,function ($a,$b) {
            if ($a['last_message']['created_time'] == $b['last_message']['created_time']) return 0;
            return ($a['last_message']['created_time'] < $b['last_message']['created_time'])? 1 : -1;
        });
        array_unshift($im_list,$customer_message);
        $last_task = $user->tasks()->where('status',0)->orderBy('priority','DESC')->latest()->first();
        $format_last_task = '';
        if ($last_task) {
            $format_last_task = TaskLogic::formatList([$last_task]);
        }
        $notice_last_message = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->select('id','type','data','read_at','created_at')->first();
        if ($notice_last_message) {
            $notice_last_message = $notice_last_message->toArray();
            $notice_last_message['created_at'] = timestamp_format($notice_last_message['created_at']);
        }
        $task_last_message = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->select('id','type','data','read_at','created_at')->first();
        if ($task_last_message) {
            $task_last_message = $task_last_message->toArray();
            $task_last_message['created_at'] = timestamp_format($task_last_message['created_at']);
        }
        $readhub_last_message = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->select('id','type','data','read_at','created_at')->first();
        if ($readhub_last_message) {
            $readhub_last_message = $readhub_last_message->toArray();
            $readhub_last_message['created_at'] = timestamp_format($readhub_last_message['created_at']);
        }
        $money_last_message = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->select('id','type','data','read_at','created_at')->first();
        if ($money_last_message) {
            $money_last_message = $money_last_message->toArray();
            $money_last_message['created_at'] = timestamp_format($money_last_message['created_at']);
        }
        $data = [
            'todo_tasks' => $todo_task,
            'total_unread_count' => $total_unread,
            'todo_task_message' => [
                'unread_count' => $todo_task,
                'last_message' => $format_last_task?$format_last_task[0]:null
            ],
            'notice_message' => [
                'unread_count' => $notice_unread_count,
                'last_message' => $notice_last_message
            ],
            'task_message'   => [
                'unread_count' => $task_notice_unread_count,
                'last_message' => $task_last_message
            ],
            'readhub_message' => [
                'unread_count' => $readhub_unread_count,
                'last_message' => $readhub_last_message
            ],
            'money_message'   => [
                'unread_count' => $money_unread_count,
                'last_message' => $money_last_message
            ],
            'im_messages' => $im_list
        ];

        return self::createJsonData(true,$data);
    }


    //推送设置
    public function pushSettings(Request $request){
        $user = $request->user();
        /**
         * @var NotificationSettings $settings
         */
        $settings = $user->notificationSettings();
        $fields = $this->notificationSettings;
        foreach ($fields as $field=>$value) {
            if (-1 != $request->input($field,-1)) {
                $settings->set($field,$request->input($field));
            }
        }
        $settings->persist();
        return self::createJsonData(true,$settings->all());
    }

    //获取推送设置信息
    public function getPushSettings(Request $request) {
        $user = $request->user();
        $data = $this->notificationSettings;
        foreach ($data as $field=>$value) {
            if (isset($user->site_notifications[$field])) {
                $data[$field] = $user->site_notifications[$field];
            }
        }
        return self::createJsonData(true,$data);
    }


}
