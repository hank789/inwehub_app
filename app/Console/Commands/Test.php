<?php

namespace App\Console\Commands;

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
        $user = Question::find(1);
        $tags = $user->tags()->where('category_id',3)->pluck('name')->toArray();
        var_dump(implode(',',$tags));
    }
}
