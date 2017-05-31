<?php namespace App\Console\Commands\Question;
/**
 * @author: wanghui
 * @date: 2017/5/31 下午4:53
 * @email: wanghui@yonglibao.com
 */

use App\Models\Question;
use Illuminate\Console\Command;

class InvitationOvertimeAlert extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'question:invitation:alert:overtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '邀请超时提醒';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $minute = Setting()->get('question_invite_unanswer_alert_minute',10);
        $overdue_time = date('Y-m-d H:i:s',strtotime('+'.$minute.' minutes'));
        $questions = Question::whereIn('status',[2,4])->where('updated_at','>=',$overdue_time)->get();
        foreach($questions as $question){

        }
    }
}