<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Task;
use Illuminate\Console\Command;

class FixTaskPriority extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:task:priority';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修复任务优先级';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $tasks = Task::get();
        foreach ($tasks as $task) {
            $task->priority = Task::$actionPriority[$task->action]['priority'];
            $task->save();
        }
    }

}