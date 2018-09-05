<?php namespace App\Console\Commands\Crontab;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: hank.huiwang@gmail.com
 */

use App\Models\Answer;
use App\Models\Feedback;
use App\Models\Task;
use Illuminate\Console\Command;

class DealOvertimeTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crontab:deal-overtime-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理超时任务';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = Task::where('action',Task::ACTION_TYPE_ANSWER_FEEDBACK)
            ->where('status',0)
            ->where('created_at','<',date('Y-m-d H:i:s',strtotime('-15 days')))->get();
        foreach ($tasks as $task) {
            $answer = Answer::find($task->source_id);
            if ($answer) {
                $feedback = Feedback::create([
                    'user_id' => $task->user_id,
                    'source_id' => $task->source_id,
                    'source_type' => $task->source_type,
                    'star' => 5,
                    'to_user_id' => $answer->user_id,
                    'content' => '5星好评',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            $task->status = 1;
            $task->save();
        }
    }

}