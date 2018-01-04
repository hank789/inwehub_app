<?php

namespace App\Jobs;

use App\Models\Doing;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class SaveActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;

    public $data;



    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $this->data['subject'] = str_limit($this->data['subject'],86,'');
            $this->data['content'] = str_limit($this->data['content'],200,'');
            $this->data['refer_content'] = str_limit($this->data['refer_content'],200,'');
            Doing::create($this->data);
        }catch (\Exception $e){
            app('sentry')->captureException($e);
        }
    }
}
