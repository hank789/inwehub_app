<?php namespace App\Logic;
use App\Models\Doing;
use App\Models\Task;
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

}