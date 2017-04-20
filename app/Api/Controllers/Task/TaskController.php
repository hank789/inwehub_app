<?php namespace App\Api\Controllers\Task;
use App\Api\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * @author: wanghui
 * @date: 2017/4/20 下午8:40
 * @email: wanghui@yonglibao.com
 */

class TaskController extends Controller {

    public function myList(Request $request){
        $last_id = $request->input('last_id',0);
        $tasks = $request->user()->tasks()->where('status',0)->where('id','>',$last_id)->orderBy('updated_at','DESC')->paginate(10);
        $list = [];
        foreach($tasks as $task){
            $task_type = '';
            $task_type_description = '';
            $description = '';
            switch($task->source_type){
                case 'App\Models\Question':
                    $task_type = 1;
                    $task_type_description = '提问';
                    $question = Question::find($task->source_id);
                    $description = $question->title;
                    $status = $question->status;
                    switch($question->status){
                        case 2:
                            //已分配待确认
                            $status_description = '您的问题来啦,请速速点击前往应答';
                            break;
                        case 4:
                            //已确认待回答
                            $answer = Answer::where('status',3)->first();
                            $answer_promise_time = $answer->promise_time;
                            $status_description = promise_time_format($answer_promise_time).',点击前往回答';
                            break;
                        case 6:
                            //已回答待点评
                            $status_description = '您已提交回答,等待对方评价';
                            break;
                        case 7:
                            //已点评
                            $status_description = '对方已点评,点击前往查看评价';
                            break;
                    }
                    break;
            }
            $list[] = [
                'task_type' => $task_type,
                'task_type_description' => $task_type_description,
                'user_name' => $task->user->name,
                'user_avatar_url' => $task->user->getAvatarUrl(),
                'description' => $description,
                'status' => $status,
                'status_description' => $status_description,
                'created_at' => (string)$task->updated_at
            ];
        }
        return self::createJsonData(true,$list);
    }

}