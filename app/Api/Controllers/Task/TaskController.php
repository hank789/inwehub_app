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
            $priority = '高';
            $deadline = '';
            $status_description = '';
            $status = $task->status;
            $user_name = '';
            $user_avatar_url = '';
            switch($task->source_type){
                case 'App\Models\Question':
                    $task_type = 1;
                    $task_type_description = '专业问答';
                    $question = Question::find($task->source_id);
                    $object_id = $question->id;
                    $status = $question->status;
                    switch($task->action){
                        case Task::ACTION_TYPE_ANSWER:
                            //已分配待确认
                            $user_name = $question->hide ? '匿名' : $question->user->name;
                            $user_avatar_url = $question->hide ? config('image.user_default_avatar') : $question->user->getAvatarUrl();
                            $description = '用户'.$user_name.'发起了专业提问:'.$question->title;
                            $answer = Answer::where('question_id',$object_id)->where('user_id',$task->user_id)->get()->last();
                            if($answer && $answer->status == 3){
                                $status_description = '前往回答问题';
                                $deadline = $answer->promise_time;
                            }else{
                                $status_description = '前往确认回答';
                            }
                            break;
                    }
                    break;
                case 'App\Models\Answer':
                    $task_type = 2;
                    $task_type_description = '专业问答';
                    $answer = Answer::find($task->source_id);
                    $question = Question::find($answer->question_id);
                    $object_id = $question->id;
                    $status = $question->status;
                    switch($task->action){
                        case Task::ACTION_TYPE_ANSWER_FEEDBACK:
                            $user_name = $answer->user->name;
                            $user_avatar_url = $answer->user->getAvatarUrl();
                            $priority = '中';
                            $description = '用户'.$user_name.'回答了您的专业提问:'.$question->title;
                            $status_description = '前往点评';
                            break;
                    }
                    break;
                case 'newbie_complete_userinfo':
                    //新手完善用户信息
                    $task_type = 3;
                    $description = '个人信息保持90%以上完整度';
                    break;
                case 'newbie_readhub_comment':
                    //新手阅读提问
                    $task_type = 4;
                    $description = '前往发现阅读，参与评论';
                    break;
                case 'newbie_ask':
                    //新手提问
                    $task_type = 5;
                    $description = '送你首次提问1元特惠券';
                    break;
            }
            $list[] = [
                'id'        => $task->id,
                'task_type' => $task_type,
                'task_type_description' => $task_type_description,
                'user_name' => $user_name,
                'user_avatar_url' => $user_avatar_url,
                'description' => $description,
                'status_description' => $status_description,
                'object_id'   => $object_id,
                'status' => $status,
                'created_at' => (string)$task->updated_at,
                'deadline' =>$deadline,
                'priority' => $priority
            ];
        }
        return self::createJsonData(true,['list'=>$list,'total'=>$request->user()->tasks->where('status',0)->count()]);
    }

}