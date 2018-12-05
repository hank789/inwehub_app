<?php
/**
 * @author: wanghui
 * @date: 2018/12/5 下午7:25
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Jobs;
use App\Models\UserTag;
use App\Services\RateLimiter;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LogUserViewTags implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public $user_id;

    public $source;



    public function __construct($user_id, $source)
    {
        $this->user_id = $user_id;
        $this->source = $source;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->source->increment('views');
        if(RateLimiter::STATUS_GOOD == RateLimiter::instance()->increase('log_user_view_tags',$this->user_id,30)){
            if ($this->user_id > 0) {
                UserTag::multiIncrement($this->user_id,$this->source->tags()->get(),'views');
            }
        }
    }

}