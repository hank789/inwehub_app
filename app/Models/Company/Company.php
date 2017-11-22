<?php namespace App\Models\Company;

use App\Models\Relations\BelongsToUserTrait;
use App\Models\Relations\MorphManyTagsTrait;
use App\Models\UserData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Company\Company
 *
 * @mixin \Eloquent
 * @property int $user_id
 * @property string $company_name 公司名字
 * @property string $company_workers 公司人数
 * @property string $company_credit_code 统一社会信用代码
 * @property string $company_bank 开户银行
 * @property string $company_bank_account 开户账户
 * @property string $company_address 公司地址
 * @property string $company_work_phone 公司电话
 * @property int $company_represent_person_type 公司对接人类型,0为其他人,1为当前用户
 * @property string|null $company_represent_person_name 公司对接人姓名
 * @property string|null $company_represent_person_title 公司对接人职位
 * @property string|null $company_represent_person_phone 公司对接人手机号
 * @property string|null $company_represent_person_email 公司对接人邮箱
 * @property int $company_auth_mode 公司认证模式,1为协议验证,2为打款验证
 * @property int $apply_status 认证状态:1待认证,2认证成功,3认证失败
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property-read \App\Models\User $user
 * @property-read \App\Models\UserData $userData
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\Company onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereApplyStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyAuthMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyBankAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyCreditCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyRepresentPersonEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyRepresentPersonName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyRepresentPersonPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyRepresentPersonTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyRepresentPersonType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyWorkPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCompanyWorkers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\Company whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\Company withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Company\Company withoutTrashed()
 */
class Company extends Model
{
    use BelongsToUserTrait,MorphManyTagsTrait,SoftDeletes;
    protected $table = 'company';
    protected $primaryKey = 'user_id';

    protected $fillable = ['user_id', 'company_name','company_workers','company_credit_code','company_bank','company_bank_account',
        'company_address','company_work_phone','company_represent_person_type','company_represent_person_name',
        'company_represent_person_title','company_represent_person_phone','company_represent_person_email','company_auth_mode','apply_status'];


    const APPLY_STATUS_PENDING = 1;
    const APPLY_STATUS_SUCCESS = 2;
    const APPLY_STATUS_REJECT = 3;

    public static function boot()
    {
        parent::boot();


        static::updating(function($company){
            UserData::where('user_id',$company->user_id)->update(
                ['is_company'=>($company->apply_status == self::APPLY_STATUS_SUCCESS ? 1:0) ]
            );
        });

    }

    public function userData()
    {
        return $this->belongsTo('App\Models\UserData','user_id','user_id');
    }

}
