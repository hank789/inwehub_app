<?php

namespace App\Api\Controllers;

use App\Exceptions\ApiException;
use App\Models\IM\Conversation;
use App\Models\Notification;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NotificationController extends Controller
{

    public function readhubList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }

    public function taskList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }

    public function noticeList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('api_data_page_size'))->toArray();
        return self::createJsonData(true, $data);
    }

    public function moneyList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->select('id','type','data','read_at','created_at')->simplePaginate(Config::get('api_data_page_size'))->toArray();
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

        $total_unread =  $notice_unread_count + $task_notice_unread_count + $readhub_unread_count + $money_unread_count;
        $im_messages = Conversation::where('user_id',$user->id)->groupBy('contact_id')->get();

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

        foreach ($im_messages as $im_message) {
            $contact = User::find($im_message->contact_id);
            $im_count = $user->conversations()->where('contact_id', $im_message->contact_id)->where('im_messages.user_id',$im_message->contact_id)->whereNull('read_at')->count();
            $total_unread += $im_count;
            $last_message = $user->conversations()->where('contact_id', $im_message->contact_id)->orderBy('im_conversations.id','DESC')->first();
            $item = [
                'unread_count' => $im_count,
                'avatar'       => $contact->avatar,
                'name'         => $contact->name,
                'contact_id'   => $contact->id,
                'contact_uuid' => $contact->uuid,
                'last_message' => [
                    'id' => $last_message->id,
                    'text' => '',
                    'data'  => $last_message->data,
                    'read_at' => $last_message->read_at,
                    'created_at' => (string)$last_message->created_at
                ]
            ];
            if ($im_message->contact_id == $customer_id) {
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
        $im_list[-1] = $customer_message;
        $data = [
            'todo_tasks' => $todo_task,
            'total_unread_count' => $total_unread,
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

}
