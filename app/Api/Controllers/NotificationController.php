<?php

namespace App\Api\Controllers;

use App\Events\Frontend\Notification\MarkAsRead;
use App\Events\Frontend\System\ImportantNotify;
use App\Exceptions\ApiException;
use App\Http\Controllers\Admin\OperateController;
use App\Logic\TaskLogic;
use App\Models\Doing;
use App\Models\Groups\Group;
use App\Models\Groups\GroupMember;
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
use Illuminate\Support\Facades\Cache;
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
        'push_do_not_disturb' => 0,
        'push_daily_subscribe' => 0,
        'email_daily_subscribe' => 0,
        'wechat_daily_subscribe' => 0
    ];

    public function readhubList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();
        Cache::delete('user_notification_count_'.$user->id);
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
        Cache::delete('user_notification_count_'.$user->id);
        return self::createJsonData(true, $data);
    }

    public function moneyList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('inwehub.api_data_page_size'))->toArray();
        Cache::delete('user_notification_count_'.$user->id);
        return self::createJsonData(true, $data);
    }


    public function markAsRead(Request $request)
    {
        $notification_type = $request->input('notification_type',0);
        $user = $request->user();
        event(new MarkAsRead($user->id,$notification_type));
        return self::createJsonData(true);
    }

    public function count(Request $request){
        $user = $request->user();
        $data = Cache::get('user_notification_count_'.$user->id);
        $need_report = $request->input('need_report',0);
        if ($need_report) {
            $this->doing($user,Doing::ACTION_VIEW_NOTIFICATION_LIST,'',0,'核心页面');
        }
        if (!$data) {
            $todo_task = $user->tasks()->where('status',0)->count();
            $notice_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->count();
            $task_notice_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->count();
            $readhub_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->count();
            $money_unread_count = $user->unreadNotifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->count();

            $total_unread =  $notice_unread_count + $task_notice_unread_count + $readhub_unread_count + $money_unread_count + $todo_task;
            $im_rooms = Room::where('source_type',User::class)->where(function ($query) use ($user) {$query->where('user_id',$user->id)->orWhere('source_id',$user->id);})->take(50)->get();

            $im_list = [];
            $is_kefu_in = false;
            //客服
            $customer_id = Role::getCustomerUserId();
            $customer_user = User::find($customer_id);
            $customer_message = [];

            foreach ($im_rooms as $im_room) {
                $last_message = MessageRoom::where('room_id',$im_room->id)->orderBy('id','desc')->first();
                $contact = User::find($im_room->user_id==$user->id?$im_room->source_id:$im_room->user_id);
                if (!$contact) continue;
                $item = [
                    'unread_count' => 0,
                    'avatar'       => $contact->avatar,
                    'name'         => $contact->name,
                    'room_id'      => $im_room->id,
                    'room_type'    => Room::ROOM_TYPE_WHISPER,
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
                $roomUser = RoomUser::where('user_id',$user->id)->where('room_id',$im_room->id)->first();
                if ($roomUser) {
                    $item['unread_count'] = MessageRoom::where('room_id',$im_room->id)->where('message_id','>',$roomUser->last_msg_id)->count();
                    $total_unread += $item['unread_count'];
                }
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
                    'room_type'    => Room::ROOM_TYPE_WHISPER,
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
                    $last_message = MessageRoom::where('room_id',$room->id)->orderBy('id','desc')->first();
                    $item = [
                        'unread_count' => 0,
                        'avatar'       => $group->logo,
                        'name'         => $group->name,
                        'room_id'      => $room->id,
                        'room_type'    => Room::ROOM_TYPE_GROUP,
                        'contact_id'   => 0,
                        'contact_uuid' => null,
                        'last_message' => [
                            'id' => $last_message?$last_message->message_id:0,
                            'text' => '',
                            'data'  => $last_message?$last_message->message->data:['text'=>'','img'=>''],
                            'read_at' => $last_message?$last_message->message->read_at:'',
                            'created_at' => $last_message?(string)$last_message->created_at:''
                        ]
                    ];
                    $roomUser = RoomUser::where('user_id',$user->id)->where('room_id',$room->id)->first();
                    if ($roomUser) {
                        $item['unread_count'] = MessageRoom::where('room_id',$room->id)->where('message_id','>',$roomUser->last_msg_id)->count();
                        $total_unread += $item['unread_count'];
                    }
                    $im_list[] = $item;
                }
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
            Cache::put('user_notification_count_'.$user->id,$data,120);
        }


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
        $notify = [];
        foreach ($fields as $field=>$value) {
            if (-1 != $request->input($field,-1)) {
                $settings->set($field,$request->input($field));
                $notify[$field] = $request->input($field);
            }
        }
        $settings->persist();
        $msg = '';
        foreach ($notify as $key=>$val) {
            $title = '';
            switch ($key) {
                case 'push_daily_subscribe':
                    $title = '推送订阅:'.($val?'开启':'关闭');
                    $msg = ($val?'已开启':'已关闭').'推送订阅';
                    break;
                case 'email_daily_subscribe':
                    $title = '邮件订阅:'.($val?:'关');
                    $msg = ($val?'已开启':'已关闭').'邮件订阅';
                    break;
                case 'wechat_daily_subscribe':
                    $title = '微信服务号订阅:'.($val?'开启':'关闭');
                    $msg = ($val?'已开启':'已关闭').'微信阅';
                    break;
            }
            if ($title) {
                event(new ImportantNotify(formatSlackUser($user).'用户设置'.$title));
            }
        }

        return self::createJsonData(true,$settings->all(),ApiException::SUCCESS,$msg);
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
