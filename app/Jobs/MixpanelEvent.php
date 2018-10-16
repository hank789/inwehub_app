<?php
/**
 * @author: wanghui
 * @date: 2018/10/16 下午7:14
 * @email:    hank.HuiWang@gmail.com
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MixpanelEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $uid;

    protected $page_title;

    protected $page;

    protected $page_name;

    protected $event;

    public function __construct($uid,$event,$page_title,$page,$page_name)
    {
        $this->uid = $uid;
        $this->page_title = $page_title;
        $this->page = $page;
        $this->page_name = $page_name;
        $this->event = $event;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mp = \Mixpanel::getInstance(config('app.mixpanel_token'));
        $mp->identify($this->uid);
        $mp->track($this->event,['app'=>'inwehub','user_id'=>$this->uid,'page_title'=>$this->page_title,'page'=>$this->page,'page_name'=>$this->page_name]);
    }

}