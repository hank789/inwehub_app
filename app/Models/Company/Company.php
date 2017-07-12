<?php namespace App\Models\Company;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Eloquent
 */
class Company extends Model
{
    use BelongsToUserTrait,MorphManyTagsTrait,SoftDeletes;
    protected $table = 'company';
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'company_name','company_workers','company_credit_code','company_bank','company_bank_account',
        'company_address','company_work_phone','company_represent_person_type','company_represent_person_name',
        'company_represent_person_title','company_represent_person_phone','company_represent_person_email','company_auth_mode','apply_status'];

}
