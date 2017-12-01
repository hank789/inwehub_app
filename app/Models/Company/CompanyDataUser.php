<?php namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Company\CompanyDataUser
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
 * @property int $id
 * @property int $company_data_id 公司id
 * @property int|null $status 状态 1-在职 2-项目 3-离职
 * @property int|null $is_show 是否显示 0-不显示 1-显示
 * @property int|null $audit_status 审核状态 0-未审核 1-已审核 2-未通过
 * @property-read \App\Models\Company\CompanyData $companyData
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\CompanyDataUser whereAuditStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\CompanyDataUser whereCompanyDataId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\CompanyDataUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\CompanyDataUser whereIsShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Company\CompanyDataUser whereStatus($value)
 */
class CompanyDataUser extends Model
{
    protected $table = 'company_data_user';

    protected $fillable = ['company_data_id', 'status','user_id','audit_status'];

    public $timestamps = false;

    public function companyData(){
        return $this->belongsTo('App\Models\Company\CompanyData');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function statusInfo(){
        switch ($this->status) {
            case 1:
                return '在职';
                break;
            case 2:
                return '项目';
            case 3:
                return '离职';
        }
    }

}
