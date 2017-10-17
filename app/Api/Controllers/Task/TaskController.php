<?php namespace App\Api\Controllers\Task;
use App\Api\Controllers\Controller;
use App\Logic\TaskLogic;
use App\Models\Answer;
use App\Models\Notification;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: wanghui@yonglibao.com
 */

class TaskController extends Controller {

    public function myList(Request $request){
        $top_id = $request->input('top_id',0);
        $bottom_id = $request->input('bottom_id',0);

        $query = $request->user()->tasks()->where('status',0);
        if($top_id){
            $query = $query->where('id','>',$top_id);
        }elseif($bottom_id){
            $query = $query->where('id','<',$bottom_id);
        }else{
            $query = $query->where('id','>',0);
        }
        $tasks = $query->orderBy('id','DESC')->paginate(10);
        $task_count = $tasks->count();
        $notification_count = $request->user()->unreadNotifications()->whereIn('notification_type', [
            Notification::NOTIFICATION_TYPE_NOTICE,
            Notification::NOTIFICATION_TYPE_TASK,
            Notification::NOTIFICATION_TYPE_READ,
            Notification::NOTIFICATION_TYPE_MONEY
            ])->count();
        $list = TaskLogic::formatList($tasks);

        return self::createJsonData(true,['list'=>$list,'total'=>$task_count + $notification_count]);
    }

}