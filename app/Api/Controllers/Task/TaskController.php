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
        $list = [];
        foreach($tasks as $task){
            $task_type = '';
            $task_type_description = '';
            $description = '';
            $object_id = '';
            switch($task->source_type){
                case 'App\Models\Question':
                    $task_type = 1;
                    $task_type_description = '提问';
                    $question = Question::find($task->source_id);
                    $object_id = $question->id;
                    $description = $question->title;
                    $status = $question->status;
                    switch($task->action){
                        case Task::ACTION_TYPE_ANSWER:
                            //已分配待确认
                            $status_description = '您的问题来啦,请速速点击前往应答';

                            $answer = Answer::where('question_id',$object_id)->where('user_id',$task->user_id)->get()->last();
                            if($answer && $answer->status == 3){
                                $answer_promise_time = $answer->promise_time;
                                $desc = promise_time_format($answer_promise_time);
                                $status_description = $desc['desc'].',点击前往回答';
                            }
                            break;
                    }
                    break;
                case 'App\Models\Answer':
                    $task_type = 2;
                    $task_type_description = '回答';
                    $answer = Answer::find($task->source_id);
                    $question = Question::find($answer->question_id);
                    $object_id = $question->id;
                    $description = $question->title;
                    $status = $question->status;
                    switch($task->action){
                        case Task::ACTION_TYPE_ANSWER_FEEDBACK:
                            $task_type_description = '点评';
                            $status_description = '需要前往查看回答并进行点评';
                            break;
                    }
                    break;
            }
            $list[] = [
                'id'        => $task->id,
                'task_type' => $task_type,
                'task_type_description' => $task_type_description,
                'user_name' => $task->user->name,
                'user_avatar_url' => $task->user->getAvatarUrl(),
                'description' => $description,
                'object_id'   => $object_id,
                'status' => $status,
                'status_description' => $status_description,
                'created_at' => (string)$task->updated_at
            ];
        }
        return self::createJsonData(true,$list);
    }

}