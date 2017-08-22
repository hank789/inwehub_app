<?php

namespace App\Api\Controllers;

use App\Models\Notification;
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
        $todo_task = $request->user()->tasks()->where('status',0)->count();
        $data = [
            'todo_tasks' => $todo_task
        ];

        return self::createJsonData(true,$data);
    }

}
