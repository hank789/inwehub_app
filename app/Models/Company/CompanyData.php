<?php namespace App\Models\Company;

use App\Models\Relations\MorphManyTagsTrait;
use App\Services\BaiduMap;
use App\Services\GeoHash;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Company\CompanyData
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
 */
class CompanyData extends Model
{
    use MorphManyTagsTrait;
    protected $table = 'company_data';

    protected $fillable = ['name', 'logo','is_show','address_province','address_detail','longitude','latitude','geohash','audit_status'];



    public static function boot()
    {
        parent::boot();
    }

    public static function initCompanyData($companyName,$user_id,$userCompanyStatus,$isShow = 1){
        $exist = self::where('name',$companyName)->first();
        if (!$exist) {
            $location = BaiduMap::instance()->place($companyName);
            $city = BaiduMap::instance()->placeSuggestion($companyName);
            $address_province = '';
            $address_detail = '';
            $longitude = '';
            $latitude = '';
            $hash = '';
            if (count($city['result']) >= 1) {
                $object1 = $city['result'][0];
                $address_province = $object1['city'].$object1['district'];
                $longitude = $object1['location']['lng'];
                $latitude = $object1['location']['lat'];
            }
            if ($location['total'] >= 1) {
                $object2 = $location['results'][0];
                if (isset($object2['address'])) {
                    $address_detail = $object2['address'];
                    $longitude = $object2['detail_info']['navi_location']['lng'];
                    $latitude = $object2['detail_info']['navi_location']['lat'];
                }
            }
            if ($longitude) {
                $hash = GeoHash::instance()->encode($latitude,$longitude);
            }
            $data = self::create([
                'name' => $companyName,
                'logo' => '',
                'address_province' => $address_province,
                'address_detail'   => $address_detail,
                'longitude'        => $longitude,
                'latitude'         => $latitude,
                'geohash'          => $hash,
                'audit_status'     => 1
            ]);
            CompanyDataUser::create([
                'company_data_id' => $data->id,
                'user_id'         => $user_id,
                'audit_status'    => 1,
                'is_show'         => $isShow,
                'status'          => $userCompanyStatus
            ]);
        } else {
            $existUser = CompanyDataUser::where('company_data_id',$exist->id)->where('user_id',$user_id)->first();
            if (!$existUser) {
                CompanyDataUser::create([
                    'company_data_id' => $exist->id,
                    'user_id'         => $user_id,
                    'audit_status'    => 1,
                    'is_show'         => $isShow,
                    'status'          => $userCompanyStatus
                ]);
            } else {
                $existUser->status = $userCompanyStatus;
                $exist->is_show = $isShow;
                $existUser->save();
            }
        }
    }

}
