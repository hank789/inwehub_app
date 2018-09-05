<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: hank.huiwang@gmail.com
 */

use App\Cache\UserCache;
use App\Models\Company\CompanyData;
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
        //同步公司信息
        CompanyData::initCompanyData($jobInfo->company,$jobInfo->user_id,$jobInfo->end_time == '至今' ? 1:3);
    }

    public function updated(JobInfo $jobInfo){
        UserCache::delUserInfoCache($jobInfo->user_id);
        CompanyData::initCompanyData($jobInfo->company,$jobInfo->user_id,$jobInfo->end_time == '至今' ? 1:3);
    }

    public function deleted(JobInfo $jobInfo){
        UserCache::delUserInfoCache($jobInfo->user_id);
    }

}