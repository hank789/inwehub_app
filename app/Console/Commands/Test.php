<?php

namespace App\Console\Commands;

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
        $t = '2017-04-20 14:45:34';

        var_dump(promise_time_format($t));
    }
}
