<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Cache\UserCache;
use App\Models\UserInfo\JobInfo;
use App\Models\UserInfo\ProjectInfo;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserProjectObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function created(ProjectInfo $projectInfo)
    {
        UserCache::delUserInfoCache($projectInfo->user_id);
    }

    public function updated(ProjectInfo $projectInfo){
        UserCache::delUserInfoCache($projectInfo->user_id);
    }

    public function deleted(ProjectInfo $projectInfo){
        UserCache::delUserInfoCache($projectInfo->user_id);
    }

}