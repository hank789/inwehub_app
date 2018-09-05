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
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: hank.huiwang@gmail.com
 */

class TaskController extends Controller {

    public function myList(Request $request){
        $query = $request->user()->tasks()->where('status',0);

        $tasks = $query->orderBy('priority','DESC')->latest()->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $task_count = $request->user()->tasks()->where('status',0)->count();
        $notification_count = $request->user()->unreadNotifications()->whereIn('notification_type', [
            Notification::NOTIFICATION_TYPE_NOTICE,
            Notification::NOTIFICATION_TYPE_TASK,
            Notification::NOTIFICATION_TYPE_READ,
            Notification::NOTIFICATION_TYPE_MONEY
            ])->count();
        $return = $tasks->toArray();
        $list = TaskLogic::formatList($tasks);
        $return['data'] = $list;
        $return['total'] = $task_count + $notification_count;

        return self::createJsonData(true,$return);
    }

}