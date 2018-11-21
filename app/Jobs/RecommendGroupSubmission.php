<?php

namespace App\Jobs;

use App\Models\Groups\GroupMember;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\NewSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;


class RecommendGroupSubmission implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $submission_id;

    public $user_id;



    public function __construct($user_id, $submission_id)
    {
        $this->submission_id = $submission_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $submission = Submission::find($this->submission_id);
        if (!$submission) return;
        if ($submission->status == 0 || empty($submission->group_id) || $submission->is_recommend <= 0) return;
        $members = GroupMember::where('group_id',$submission->group_id)->where('audit_status',GroupMember::AUDIT_STATUS_SUCCESS)->pluck('user_id')->toArray();
        $data = $submission->data;
        $data['sourceViews'] = 1;
        $submission->data = $data;
        foreach ($members as $muid) {
            $mUser = User::find($muid);
            $mUser->notify((new NewSubmission($muid,$submission)));
        }
    }
}
