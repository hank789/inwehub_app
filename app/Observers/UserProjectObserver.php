<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: hank.huiwang@gmail.com
 */

use App\Cache\UserCache;
use App\Models\Company\CompanyData;
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
        CompanyData::initCompanyData($projectInfo->customer_name,$projectInfo->user_id,2);

    }

    public function updated(ProjectInfo $projectInfo){
        UserCache::delUserInfoCache($projectInfo->user_id);
        CompanyData::initCompanyData($projectInfo->customer_name,$projectInfo->user_id,2);
    }

    public function deleted(ProjectInfo $projectInfo){
        UserCache::delUserInfoCache($projectInfo->user_id);
    }

}