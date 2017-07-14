<?php namespace App\Observers;
/**
 * @author: wanghui
 * @date: 2017/4/20 下午4:23
 * @email: wanghui@yonglibao.com
 */

use App\Jobs\Question\PromiseOvertime;
use App\Logic\QuestionLogic;
use App\Logic\QuillLogic;
use App\Models\Answer;
use App\Models\Company\Company;
use App\Models\Question;
use App\Models\QuestionInvitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyObserver implements ShouldQueue {

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 2;


    public function creating(Company $company)
    {
        $fields[] = [
            'title' => '企业',
            'value' => $company->company_name,
            'short' => true
        ];

        $fields[] = [
            'title' => '规模',
            'value' => trans_company_workers($company->company_workers),
            'short' => true
        ];

        $fields[] = [
            'title' => '对接人',
            'value' => $company->company_represent_person_name,
            'short' => true
        ];

        $fields[] = [
            'title' => '行业领域',
            'value' => implode(',',$company->tags()->pluck('name')->toArray()),
            'short' => false
        ];

        return \Slack::to(config('slack.ask_activity_channel'))
            ->disableMarkdown()
            ->attach(
                [
                    'color'     => 'good',
                    'fields' => $fields
                ]
            )->send('用户['.$company->user->name.']提交了企业认证');
    }

    public function updated(Company $company){
        $this->creating($company);
    }

}