<?php

namespace App\Jobs;

use App\Models\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class UpdateQuestionRate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $questionId;



    public function __construct($questionId)
    {
        $this->questionId = $questionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $question = Question::find($this->questionId);
        $question->calculationRate();
    }
}
