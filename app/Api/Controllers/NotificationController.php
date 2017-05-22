<?php

namespace App\Api\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class NotificationController extends Controller
{
    /**
     * 显示用户通知
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = Notification::where('to_user_id',$request->user()->id);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }

        $notifications = $query->orderBy('created_at','DESC')->paginate(10);
        $list = [];
        foreach($notifications as $notification){
            $item = [];
            $item['id'] = $notification->id;
            $item['type'] = $notification->type;
            $item['type_text'] = Config::get('intervapp.notification_types.'.$notification->type);
            $item['description'] = $notification->content;
            $item['is_read'] = $notification->is_read;
            $item['created_at'] = (string)$notification->created_at;
            $list[] = $item;
        }

        $this->readNotifications(0,'user');
        return self::createJsonData(true,$list);
    }


    public function readAll()
    {
        Notification::where('to_user_id','=',Auth()->user()->id)->where('is_read','=',0)->update(['is_read'=>1]);
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
