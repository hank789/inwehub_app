<?php namespace App\Console\Commands\FixData;
/**
 * @author: wanghui
 * @date: 2017/6/21 下午8:59
 * @email: wanghui@yonglibao.com
 */

use App\Models\Company\CompanyDataUser;
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
            $this->addData($user->company,$user->id,1);
        }
        foreach ($jobs as $job) {
            $this->addData($job->company,$job->user_id,$job->end_time == '至今' ? 1:3);
        }

        $projects = ProjectInfo::get();
        foreach ($projects as $project) {
            $this->addData($project->customer_name,$project->user_id,2);
        }
    }

    protected function addData($companyName,$user_id,$userCompanyStatus){
        $exist = CompanyDataModel::where('name',$companyName)->first();
        if (!$exist) {
            $data = CompanyDataModel::create([
                'name' => $companyName,
                'logo' => '',
                'address_province' => '',
                'address_detail'   => '',
                'longitude'        => '',
                'latitude'         => '',
                'audit_status'     => 0
            ]);
            CompanyDataUser::create([
                'company_data_id' => $data->id,
                'user_id'         => $user_id,
                'audit_status'    => 0,
                'status'          => $userCompanyStatus
            ]);
        } else {
            $existUser = CompanyDataUser::where('company_data_id',$exist->id)->where('user_id',$user_id)->first();
            if (!$existUser) {
                CompanyDataUser::create([
                    'company_data_id' => $exist->id,
                    'user_id'         => $user_id,
                    'audit_status'    => 0,
                    'status'          => $userCompanyStatus
                ]);
            }
        }
    }


}