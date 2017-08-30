<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Notification as NotificationModel;

class NotifyInwehub implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $message;
    public $type;
    public $user_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $type, $message)
    {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->user_id);
        $class = $this->type;
        $this->message['notification_type'] = NotificationModel::NOTIFICATION_TYPE_READ;
        if (class_exists($class)) {
            $user->notify(new $class($this->user_id,$this->message));
        } else {
            app('sentry')->captureException(new \Exception('class:'.$class.'不存在'));
        }
    }
}
