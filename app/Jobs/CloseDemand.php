<?php

namespace App\Jobs;

use App\Models\Weapp\Demand;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class CloseDemand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $id;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $demand = Demand::find($this->id);
        if ($demand) {
            $demand->status = Demand::STATUS_CLOSED;
            $demand->save();
        }
    }
}
