<?php namespace App\Logic;
use App\Models\Answer;
use App\Models\Doing;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

/**
 * @author: wanghui
 * @date: 2017/5/31 下午3:28
 * @email: wanghui@yonglibao.com
 */

class TaskLogic {

    /**
     * 记录用户动态
     * @param $user_id; 动态发起人
     * @param $action;  动作 ['ask','answer',...]
     * @param $source_id; 问题或文章ID
     * @param $subject;   问题或文章标题
     * @param string $content; 回答或评论内容
     * @param int $refer_id;  问题或者文章ID
     * @param int $refer_user_id; 引用内容作者ID
     * @param null $refer_content; 引用内容
     * @return static
     */
    public static function doing($user_id,$action,$source_type,$source_id,$subject,$content='',$refer_id=0,$refer_user_id=0,$refer_content=null)
    {
        try{
            return Doing::create([
                'user_id' => $user_id,
                'action' => $action,
                'source_id' => $source_id,
                'source_type' => $source_type,
                'subject' => substr($subject,0,128),
                'content' => strip_tags(substr($content,0,256)),
                'refer_id' => $refer_id,
                'refer_user_id' => $refer_user_id,
                'refer_content' => strip_tags(substr($refer_content,0,256)),
                'created_at' => Carbon::now()
            ]);
        }catch (\Exception $e){
            app('sentry')->captureException($e);
        }

    }


    /**
     * 创建任务
     * @param $user_id
     * @param $source_type
     * @param $source_id
     * @param $action
     * @param $status
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function task($user_id,$source_type,$source_id,$action,$status=0){
        try{
            return Task::create([
                'user_id' => $user_id,
                'source_id' => $source_id,
                'source_type' => $source_type,
                'action' => $action,
                'status' => $status
            ]);
        }catch (\Exception $e){
            exit($e->getMessage());
        }
    }

    public static function finishTask($source_type,$source_id,$action,$user_ids,$expert_user_ids=[]){
        $query = Task::where('source_id',$source_id)
            ->where('source_type',$source_type);
        if($user_ids) {
            $query->whereIn('user_id',$user_ids);
        }
        if($expert_user_ids){
            $query->whereNotIn('user_id',$expert_user_ids);
        }
        return $query->where('action',$action)->update(['status'=>1]);
    }


    public static function formatList($tasks){
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
                case 'App\Models\QuestionInvitation':
                    $task_type = 1;
                    $task_type_description = '邀请问答';
                    $invitation = QuestionInvitation::find($task->source_id);
                    $from_user = User::find($invitation->from_user_id);
                    $object_id = $invitation->question_id;
                    $question = Question::find($object_id);
                    $description = '用户'.$from_user->name.'邀请您回答问题:'.$question->title;
                    $status_description = '前往回答问题';
                    $user_avatar_url = $from_user->avatar;
                    $user_name = $from_user->name;
                    break;
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
                            $user_avatar_url = $question->hide ? config('image.user_default_avatar') : $question->user->avatar;
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
                            $user_avatar_url = $answer->user->avatar;
                            $priority = '中';
                            $description = '用户'.$user_name.'回答了您的专业提问:'.$question->title;
                            $status_description = '前往点评';
                            break;
                    }
                    break;
                case 'newbie_complete_userinfo':
                    //新手完善用户信息
                    $task_type_description = '新手任务';
                    $status_description = '完善顾问名片';
                    $task_type = 3;
                    $description = '个人信息保持90%以上完整度';
                    break;
                case 'newbie_readhub_comment':
                    //新手阅读回复
                    $task_type_description = '新手任务';
                    $status_description = '参与评论互动';
                    $task_type = 4;
                    $description = '前往发现阅读，参与评论';
                    break;
                case 'newbie_ask':
                    //新手提问
                    $task_type_description = '新手任务';
                    $status_description = '发起专业提问';
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
        return $list;
    }

}