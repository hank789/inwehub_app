<?php namespace App\Console\Commands\User;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Question;
use App\Models\Readhub\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Tymon\JWTAuth\JWTAuth;
use App\Logic\TaskLogic;

class NewbieTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:newbie:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '新手任务初始化';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(JWTAuth $JWTAuth)
    {
        $users = User::all();
        foreach($users as $user){
            $newbie_readhub_comment_task = Task::where('user_id',$user->id)->where('source_type','newbie_readhub_comment')->first();
            if (!$newbie_readhub_comment_task) {
                $status = 0;
                $comment = Comment::where('user_id',$user->id)->whereNull('deleted_at')->first();
                if ($comment) {
                    $status = 1;
                }
                TaskLogic::task($user->id,'newbie_readhub_comment',0,Task::ACTION_TYPE_NEWBIE_READHUB_COMMENT,$status);
            }
            $newbie_ask_task = Task::where('user_id',$user->id)->where('source_type','newbie_ask')->first();
            if (!$newbie_ask_task) {
                $status = 0;
                $question = Question::where('user_id',$user->id)->first();
                if ($question) {
                    $status = 1;
                }
                TaskLogic::task($user->id,'newbie_ask',0,'newbie_ask',$status);
            }

            $newbie_complete_userinfo_task = Task::where('user_id',$user->id)->where('source_type','newbie_complete_userinfo')->first();
            if (!$newbie_complete_userinfo_task) {
                $status = 0;
                $info_percent_score = $user->getInfoCompletePercent(false);
                $valid_percent = config('inwehub.user_info_valid_percent',90);
                if($info_percent_score >= $valid_percent){
                    $status = 1;
                }

                TaskLogic::task($user->id,'newbie_complete_userinfo',0,'newbie_complete_userinfo',$status);
            }
        }
    }

}