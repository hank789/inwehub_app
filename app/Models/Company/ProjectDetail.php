<?php namespace App\Models\Company;

use App\Models\Relations\BelongsToProjectTrait;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 * @mixin \Eloquent
 */
class ProjectDetail extends Model
{
    use BelongsToUserTrait,BelongsToProjectTrait,SoftDeletes,MorphManyTagsTrait;
    protected $table = 'project_detail';
    protected $primaryKey = 'project_id';

    protected $fillable = ['user_id', 'project_id','worker_num','worker_level','project_amount','billing_mode',
    'project_begin_time','project_cycle','work_intensity','remote_work','travel_expense','work_address','company_name',
        'company_description','company_represent_person_is_self','company_represent_person_name','company_represent_person_title',
        'company_represent_person_phone','company_represent_person_email','company_billing_title','company_billing_bank',
        'company_billing_account','company_billing_taxes','qualification_requirements','other_requirements','is_view_resume','is_apply_request'];

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    //顾问数量
    const WORKER_NUM_1 = 1;//1个
    const WORKER_NUM_2 = 2;//2个
    const WORKER_NUM_3 = 3;//3-5个
    const WORKER_NUM_4 = 4;//5-8个
    const WORKER_NUM_5 = 5;//8个以上
    const WORKER_NUM_6 = 6;//其他
    const WORKER_NUM_7 = 7;//不确定

    //顾问级别
    const WORKER_LEVEL_1 = 1;//熟练
    const WORKER_LEVEL_2 = 2;//精通
    const WORKER_LEVEL_3 = 3;//资深

    //计费模式
    const BILLING_MODE_1 = 1;//按人计费
    const BILLING_MODE_2 = 2;//整体打包

    //项目周期
    const PROJECT_CYCLE_1 = 1;//小于1周
    const PROJECT_CYCLE_2 = 2;//1-2周
    const PROJECT_CYCLE_3 = 3;//2-4周
    const PROJECT_CYCLE_4 = 4;//1-2月
    const PROJECT_CYCLE_5 = 5;//2-4月
    const PROJECT_CYCLE_6 = 6;//4-6月
    const PROJECT_CYCLE_7 = 7;//半年以上
    const PROJECT_CYCLE_8 = 8;//不确定
    const PROJECT_CYCLE_9 = 9;//其他

    //工作密度
    const WORK_INTENSITY_1 = 1;//2H/W
    const WORK_INTENSITY_2 = 2;//4H/W
    const WORK_INTENSITY_3 = 3;//8H/W
    const WORK_INTENSITY_4 = 4;//16H/W
    const WORK_INTENSITY_5 = 5;//24H/W
    const WORK_INTENSITY_6 = 6;//32H/W;
    const WORK_INTENSITY_7 = 7;//40H/W
    const WORK_INTENSITY_8 = 8;//其他
    const WORK_INTENSITY_9 = 9;//我不确定

    //远程工作
    const REMOTE_WORK_AGREE = 1;//同意远程工作
    const REMOTE_WORK_DISAGREE = 2;//不同意


    //差旅费用
    const TRAVEL_EXPENSE_1 = 1;//包含在项目内
    const TRAVEL_EXPENSE_2 = 2;//单独结算

}
