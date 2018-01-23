<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */


use App\Models\Question;
use Illuminate\Console\Command;

class FixQuestionRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:data:question:rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '修改问题排名积分';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $questions = Question::get();
        foreach ($questions as $question) {
            $question->calculationRate();
        }
    }

}