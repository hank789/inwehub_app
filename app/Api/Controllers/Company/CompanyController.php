<?php namespace App\Api\Controllers\Company;
use App\Api\Controllers\Controller;
use App\Exceptions\ApiException;
use App\Models\Company\Company;
use App\Models\Tag;
use App\Services\City\CityData;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * @author: wanghui
 * @date: 2017/6/7 上午11:28
 * @email: wanghui@yonglibao.com
 */

class CompanyController extends Controller {


    public function apply(Request $request)
    {
        $validateRules = [
            'company_name'      => 'required',
            'industry_tags'      => 'required',
            'company_workers'      => 'required',
            'company_credit_code'      => 'required',
            'company_bank'      => 'required',
            'company_bank_account'      => 'required',
            'company_address'      => 'required',
            'company_work_phone'      => 'required',
            'company_represent_person_is_self'      => 'required|in:0,1',
            'company_represent_person_name'      => 'required_if:company_represent_person_is_self,0',
            'company_represent_person_title'      => 'required_if:company_represent_person_is_self,0',
            'company_represent_person_phone'      => 'required_if:company_represent_person_is_self,0',
            'company_represent_person_email'      => 'required_if:company_represent_person_is_self,0',
            'company_auth_mode' => 'required|in:1,2'
        ];

        $this->validate($request,$validateRules);
        $user_id = $request->user()->id;

        if(RateLimiter::instance()->increase('company:apply',$user_id,3,1)){
            throw new ApiException(ApiException::VISIT_LIMIT);
        }

        $data = $request->all();
        $industry_tags = $data['industry_tags']?implode(',',$data['industry_tags']):'';

        unset($data['industry_tags']);

        $company = Company::create([
            'user_id' => $user_id,
            'company_name' => $data['company_name'],
            'company_workers' => $data['company_workers'],
            'company_credit_code' => $data['company_credit_code'],
            'company_bank' => $data['company_bank'],
            'company_bank_account' => $data['company_bank_account'],
            'company_address' => $data['company_address'],
            'company_work_phone' => $data['company_work_phone'],
            'company_represent_person_type' => $data['company_represent_person_is_self'],
            'company_represent_person_name' => $data['company_represent_person_name'],
            'company_represent_person_title' => $data['company_represent_person_title'],
            'company_represent_person_phone' => $data['company_represent_person_phone'],
            'company_represent_person_email' => $data['company_represent_person_email'],
            'company_auth_mode' => $data['company_auth_mode'],
            'apply_status' => 0
        ]);

        /*添加标签*/
        if($industry_tags){
            Tag::multiSaveByIds($industry_tags,$company);
        }

        return self::createJsonData(true,['tips'=>'企业用户申请成功']);
    }
}