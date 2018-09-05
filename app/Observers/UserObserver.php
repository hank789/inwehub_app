<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: hank.huiwang@gmail.com
 */

use App\Cache\UserCache;
use App\Models\Company\CompanyData;
use App\Models\Company\CompanyDataUser;
use App\Models\Readhub\ReadHubUser;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 1;

    public function updated(User $user){
        UserCache::delUserInfoCache($user->id);
        //同步公司信息
        //先将之前公司状态改为离职
        CompanyDataUser::where('user_id',$user->id)->where('status',1)->update(['status'=>3]);
        CompanyData::initCompanyData($user->company,$user->id,1);
    }

}