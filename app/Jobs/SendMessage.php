<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;



class SendMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $from_uid;
    public $to_uids;
    public $message;



    public function __construct($message, $from_uid, $to_uids = [])
    {
        $this->message = $message;
        $this->from_uid = $from_uid;
        $this->to_uids = $to_uids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from_user = User::find($this->from_uid);
        $data = [];
        $data['text'] = $this->message;
        $message = $from_user->messages()->create([
            'data' => $data,
        ]);

        $contact_ids = $this->to_uids;
        if (empty($this->to_uid)) {
            $contact_ids = User::where('id','!=',$this->from_uid)->select('id')->get()->pluck('id')->toArray();
        }

        foreach ($contact_ids as $contact_id) {
            $from_user->conversations()->attach($message, [
                'contact_id' => $contact_id
            ]);

            $to_user = User::find($contact_id);
            $to_user->conversations()->attach($message, [
                'contact_id' => $from_user->id,
            ]);

            // broadcast the message to the other person
            $to_user->to_slack = false;
            $to_user->notify(new NewMessage($contact_id,$message));
        }
    }
}
