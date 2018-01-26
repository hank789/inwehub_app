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
        $im_room_users = RoomUser::where('user_id',$user->id)->get();

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

        foreach ($im_room_users as $im_room_user) {
            $im_count = MessageRoom::leftJoin('im_messages','message_id','=','im_messages.id')->where('im_message_room.room_id', $im_room_user->room_id)->where('im_messages.user_id','!=',$user->id)->whereNull('im_messages.read_at')->count();
            $total_unread += $im_count;
            $last_message = MessageRoom::where('room_id',$im_room_user->room_id)->orderBy('id','desc')->first();
            $contact_room = RoomUser::where('room_id',$im_room_user->room_id)->where('user_id','!=',$user->id)->orderBy('id','desc')->first();
            if (!$contact_room) continue;
            $item = [
                'unread_count' => $im_count,
                'avatar'       => $contact_room->user->avatar,
                'name'         => $contact_room->user->name,
                'room_id'      => $im_room_user->room_id,
                'contact_id'   => $contact_room->user->id,
                'contact_uuid' => $contact_room->user->uuid,
                'last_message' => [
                    'id' => $last_message?$last_message->message_id:0,
                    'text' => '',
                    'data'  => $last_message?$last_message->message->data:['text'=>'','img'=>''],
                    'read_at' => $last_message?$last_message->message->read_at:'',
                    'created_at' => $last_message?(string)$last_message->created_at:''
                ]
            ];
            if ($contact_room->user_id == $customer_id) {
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
            if ($a['last_message']['created_at'] == $b['last_message']['created_at']) return 0;
            return ($a['last_message']['created_at'] < $b['last_message']['created_at'])? 1 : -1;
        });
        array_unshift($im_list,$customer_message);
        $last_task = $user->tasks()->where('status',0)->orderBy('priority','DESC')->latest()->first();
        $format_last_task = '';
        if ($last_task) {
            $format_last_task = TaskLogic::formatList([$last_task]);
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
                'last_message' => $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->select('id','type','data','read_at','created_at')->first()
            ],
            'task_message'   => [
                'unread_count' => $task_notice_unread_count,
                'last_message' => $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->select('id','type','data','read_at','created_at')->first()
            ],
            'readhub_message' => [
                'unread_count' => $readhub_unread_count,
                'last_message' => $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->select('id','type','data','read_at','created_at')->first(),
            ],
            'money_message'   => [
                'unread_count' => $money_unread_count,
                'last_message' => $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->select('id','type','data','read_at','created_at')->first(),
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
        $fields = [
            'push_system_notify',
            'push_rel_mine_upvoted',
            'push_rel_mine_followed',
            'push_rel_mine_mentioned',
            'push_rel_mine_commented',
            'push_rel_mine_invited',
            'push_my_user_new_activity',
            'push_my_question_new_answered',
            'push_do_not_disturb'
        ];
        foreach ($fields as $field) {
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
        return self::createJsonData(true,$user->site_notifications);
    }


}
