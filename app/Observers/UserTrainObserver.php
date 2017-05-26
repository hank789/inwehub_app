<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Cache\UserCache;
use App\Models\UserInfo\EduInfo;
use App\Models\UserInfo\JobInfo;
use App\Models\UserInfo\TrainInfo;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserTrainObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;


    public function created(TrainInfo $trainInfo)
    {
        UserCache::delUserInfoCache($trainInfo->user_id);
    }

    public function updated(TrainInfo $trainInfo){
        UserCache::delUserInfoCache($trainInfo->user_id);
    }

    public function deleted(TrainInfo $trainInfo){
        UserCache::delUserInfoCache($trainInfo->user_id);
    }

}