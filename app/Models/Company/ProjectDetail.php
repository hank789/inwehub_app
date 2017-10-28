<?php namespace App\Models\Company;

use App\Models\Relations\BelongsToProjectTrait;
use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Feedback
 *
 * @mixin \Eloquent
 * @property int $project_id
 * @property int $user_id
 * @property int $worker_num 顾问数量
 * @property int $worker_level 顾问级别
 * @property string $project_amount 项目预算,单位万
 * @property int $billing_mode 计费模式
 * @property string $project_begin_time 项目开始时间
 * @property int $project_cycle 项目周期
 * @property int $work_intensity 工作密度
 * @property int $remote_work 是否接受远程工作
 * @property int $travel_expense 差旅费用模式
 * @property string $work_address 工作地点
 * @property string $company_name 企业名称
 * @property string $company_description 企业简介
 * @property int $company_represent_person_is_self 对接人是否本人
 * @property string $company_represent_person_name 对接人姓名
 * @property string $company_represent_person_title 对接人职位
 * @property string $company_represent_person_phone 对接人手机
 * @property string $company_represent_person_email 对接人邮箱
 * @property string $company_billing_title 发票抬头信息
 * @property string $company_billing_bank 开户银行
 * @property string $company_billing_account 开户账户
 * @property string $company_billing_taxes 纳税识别号
 * @property string $qualification_requirements 认证资质
 * @property string $other_requirements 其它资质
 * @property int $is_view_resume 是否需要查看顾问简历
 * @property int $is_apply_request 是否需要顾问投递申请
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Company\Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\ProjectDetail onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereBillingMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyBillingAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyBillingBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyBillingTaxes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyBillingTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyRepresentPersonEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyRepresentPersonIsSelf($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyRepresentPersonName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyRepresentPersonPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCompanyRepresentPersonTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereIsApplyRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereIsViewResume($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereOtherRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereProjectAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereProjectBeginTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereProjectCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereQualificationRequirements($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereRemoteWork($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereTravelExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereWorkAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereWorkIntensity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereWorkerLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\ProjectDetail whereWorkerNum($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\ProjectDetail withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\ProjectDetail withoutTrashed()
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
