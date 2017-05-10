<?php

namespace App\Console\Commands;

use App\Events\Frontend\System\Push;
use App\Models\Answer;
use App\Models\Authentication;
use App\Models\Question;
use App\Models\User;
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
        $user = User::find(21);
        event(new Push($user,'有人向您发起了回答邀请','content:问题内容','body:问题body',['type'=>'question','id'=>23]));

    }
}
