<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Cache\UserCache;
use App\Models\UserInfo\JobInfo;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserJobObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function created(JobInfo $jobInfo)
    {
        UserCache::delUserInfoCache($jobInfo->user_id);
    }

    public function updated(JobInfo $jobInfo){
        UserCache::delUserInfoCache($jobInfo->user_id);
    }

    public function deleted(JobInfo $jobInfo){
        UserCache::delUserInfoCache($jobInfo->user_id);
    }

}