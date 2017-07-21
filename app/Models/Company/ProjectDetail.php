<?php namespace App\Models\Company;

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
    use BelongsToUserTrait,SoftDeletes;
    protected $table = 'project_detail';
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
}
