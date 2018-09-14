<?php

namespace App\Jobs;

use App\Models\RecommendRead;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class UpdateSubmissionKeywords implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $submissionId;



    public function __construct($submissionId)
    {
        $this->submissionId = $submissionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $submission = Submission::find($this->submissionId);
        $submission->setKeywordTags();
        $recommendRead = RecommendRead::where('source_id',$this->submissionId)->where('source_type',Submission::class)->first();
        if ($recommendRead) {
            $recommendRead->setKeywordTags();
        }
    }
}
