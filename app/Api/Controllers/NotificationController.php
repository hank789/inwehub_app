<?php

namespace App\Api\Controllers;

use App\Models\IM\Conversation;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function readhubList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_READ)->select('id','type','data','read_at','created_at')->simplePaginate(10)->toArray();
        return self::createJsonData(true, $data);
    }

    public function taskList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_TASK)->select('id','type','data','read_at','created_at')->simplePaginate(10)->toArray();
        return self::createJsonData(true, $data);
    }

    public function noticeList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_NOTICE)->select('id','type','data','read_at','created_at')->simplePaginate(10)->toArray();
        return self::createJsonData(true, $data);
    }

    public function moneyList(Request $request){
        $user = $request->user();
        $data = $user->notifications()->where('notification_type', Notification::NOTIFICATION_TYPE_MONEY)->select('id','type','data','read_at','created_at')->simplePaginate(10)->toArray();
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

        $total_unread = $todo_task + $notice_unread_count + $task_notice_unread_count + $readhub_unread_count + $money_unread_count;
        $im_messages = Conversation::where('user_id',$user->id)->groupBy('contact_id')->get();

        $im_list = [];
        foreach ($im_messages as $im_message) {
            $contact = User::find($im_message->contact_id);
            $im_count = $user->conversations()->where('contact_id', $im_message->contact_id)->where('im_messages.user_id',$im_message->contact_id)->whereNull('read_at')->count();
            $total_unread += $im_count;
            $im_list[] = [
                'unread_count' => $im_count,
                'avatar'       => $contact->avatar,
                'name'         => $contact->name,
                'last_message' => [
                    'id' => $im_message->last_message->id,
                    'text' => $im_message->last_message->data['text'],
                    'read_at' => $im_message->last_message->read_at,
                    'created_at' => (string)$im_message->last_message->created_at
                ]
            ];
        }

        $data = [
            'todo_tasks' => $total_unread,
            'total_unread_count' => 0,
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
