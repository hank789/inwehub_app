<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Authentication;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $otherAnswers = Answer::where('question_id',1)->where('status','!=',2)->first();
        if(!$otherAnswers){
            //问题已拒绝
            var_dump(1);
        }
    }
}
