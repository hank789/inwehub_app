<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\User;
use App\Models\UserInfo\JobInfo;
use App\Models\UserInfo\ProjectInfo;
use Illuminate\Console\Command;
use App\Models\Company\CompanyData as CompanyDataModel;

class CompanyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:company:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '公司数据同步';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $jobs = JobInfo::get();
        $users = User::get();
        foreach ($users as $user) {
            CompanyDataModel::initCompanyData($user->company,$user->id,1);
        }
        foreach ($jobs as $job) {
            CompanyDataModel::initCompanyData($job->company,$job->user_id,$job->end_time == '至今' ? 1:3);
        }

        $projects = ProjectInfo::get();
        foreach ($projects as $project) {
            CompanyDataModel::initCompanyData($project->customer_name,$project->user_id,2);
        }
    }



}