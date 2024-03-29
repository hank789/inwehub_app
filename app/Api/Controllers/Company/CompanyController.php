<?php namespace App\Api\Controllers\Company;
use App\Api\Controllers\Controller;
use App\Events\Frontend\System\SystemNotify;
use App\Exceptions\ApiException;
use App\Logic\TagsLogic;
use App\Models\Attention;
use App\Models\Company\Company;
use App\Models\Company\CompanyData;
use App\Models\Company\CompanyDataUser;
use App\Models\Company\CompanyService;
use App\Models\Tag;
use App\Models\TagCategoryRel;
use App\Services\BaiduMap;
use App\Services\GeoHash;
use App\Services\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

/**
 * @author: wanghui
 * @date: 2017/6/7 上午11:28
 * @email: hank.huiwang@gmail.com
 */

class CompanyController extends Controller {


    public function apply(Request $request)
    {
        $validateRules = [
            'company_name'      => 'required',
            'industry_tags'      => 'required',
            'company_workers'      => 'required|in:1,2,3,4,5',
            'company_credit_code'      => 'required',
            'company_bank'      => 'required',
            'company_bank_account'      => 'required',
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

        $newData = [
            'user_id' => $user_id,
            'company_name' => $data['company_name'],
            'company_workers' => $data['company_workers'],
            'company_credit_code' => $data['company_credit_code'],
            'company_bank' => $data['company_bank'],
            'company_bank_account' => $data['company_bank_account'],
            'company_address' => $data['company_address']??'',
            'company_work_phone' => $data['company_work_phone']??'',
            'company_represent_person_type' => $data['company_represent_person_is_self'],
            'company_represent_person_name' => $data['company_represent_person_name'],
            'company_represent_person_title' => $data['company_represent_person_title'],
            'company_represent_person_phone' => $data['company_represent_person_phone'],
            'company_represent_person_email' => $data['company_represent_person_email'],
            'company_auth_mode' => $data['company_auth_mode'],
            'apply_status' => Company::APPLY_STATUS_PENDING
        ];
        $company = Company::find($user_id);
        if($company && $company->apply_status != Company::APPLY_STATUS_REJECT){
            throw new ApiException(ApiException::USER_COMPANY_APPLY_REPEAT);
        } elseif($company){
            $company->update($newData);
        } else {
            $company = Company::create($newData);
        }

        /*添加标签*/
        if($industry_tags){
            Tag::multiSaveByIds($industry_tags,$company);
        }
        return self::createJsonData(true,['tips'=>'企业用户申请成功']);
    }

    public function applyInfo(Request $request){
        $user_id = $request->user()->id;
        $company = Company::findOrNew($user_id);
        $return = $company->toArray();
        $return['industry_tags'] = TagsLogic::formatTags($company->tags()->get());
        $return['company_represent_person_is_self'] = $return['company_represent_person_type']??0;
        $return['company_workers'] = ['value'=>$return['company_workers']??0,'text'=>trans_company_workers($return['company_workers']??0)];

        return self::createJsonData(true,$return);
    }


    public function serviceList() {
        $services = CompanyService::where('audit_status',1)->orderBy('sort','desc')->simplePaginate(Config::get('inwehub.api_data_page_size'));
        return self::createJsonData(true, $services->toArray());
    }

    public function applyService(Request $request) {
        $this->validate($request, [
            'service_title'          => 'required|min:2'
        ]);
        $user = $request->user();
        $fields = [];
        $fields[] = [
            'title'=>'服务名称',
            'value'=>$request->input('service_title')
        ];
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.'申请了企业服务',$fields));
        return self::createJsonData(true,['tips'=>'申请成功，请耐心等待']);
    }


    //附近企业搜索
    public function nearbySearch(Request $request) {
        $name = $request->input('name');
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $page = $request->input('page',1);
        if ($longitude) {
            $geohash = new GeoHash();

            $hash = $geohash->encode($latitude, $longitude);

            // 决定查询范围，值越大，获取的范围越小
            // 当geohash base32编码长度为8时，精度在19米左右，而当编码长度为9时，精度在2米左右，编码长度需要根据数据情况进行选择。
            $pre_hash = substr($hash, 0, 3);

            //取出相邻八个区域
            $neighbors = $geohash->neighbors($pre_hash);
            array_push($neighbors, $pre_hash);

            $values = '';
            foreach ($neighbors as $key=>$val) {
                $values .= '\'' . $val . '\'' .',';
            }
            $values = substr($values, 0, -1);
        }

        $query = CompanyData::where('audit_status',1);
        if ($name) {
            $query = $query->where('name','like','%'.$name.'%');
        }elseif ($longitude) {
            $query = $query->whereRaw('LEFT(`geohash`,3) IN ('.$values.')');
        }
        $companies = $query->orderBy('geohash','asc')->get();
        $per_page = 30;
        $return = [
            'current_page' => $page,
            'next_page_url' => null,
            'per_page'     => $per_page,
            'from'         => ($page-1) * $per_page + 1,
            'to'           => $page * $per_page,
            'data'         => []
        ];
        $data = [];
        foreach ($companies as $company) {
            $tags = $company->tags()->pluck('name')->toArray();
            if (empty($longitude) || !is_numeric($company->longitude) || !is_numeric($company->latitude)) {
                $distance = '未知';
            } else {
                $distance = getDistanceByLatLng($company->longitude,$company->latitude,$longitude,$latitude);
                $distance = bcadd($distance,0,0);
            }
            $data[] = [
                'id' => $company->id,
                'name' => $company->name,
                'logo' => $company->logo,
                'address_province' => $company->address_province,
                'tags' => $tags,
                'distance' => $distance,
                'distance_format' => distanceFormat($distance),
                'longitude' => $company->longitude,
                'latitude'  => $company->latitude
            ];
        }

        usort($data,function ($a,$b) {
            if ($a['distance'] == '未知') return -1;
            if ($a['distance'] == $b['distance']) return 0;
            return ($a['distance'] < $b['distance'])? -1 : 1;
        });
        $pageData = array_chunk($data,$per_page);
        $return['data'] = $pageData[$page-1]??[];
        if (empty($return['data']) && $request->input('page',1) == 1 && $request->input('searchRule') == 2) {
            $ip = $request->getClientIp();
            $location = $this->findIp($ip);
            $result = BaiduMap::instance()->place($name,0,$location[1]??'上海',$latitude ? $latitude.','.$longitude:'',0,'',2,20,0,'公司企业');
            $data = $result['results'];
            foreach ($data as $item) {
                $return['data'][] = [
                    'id'   => -1,
                    'name' => $item['name'],
                    'logo' => '',
                    'address_province' => $item['address']??'',
                    'tags' => [],
                    'distance' => '未知',
                    'distance_format' => '未知',
                    'longitude' => $item['detail_info']['navi_location']['lng']??'',
                    'latitude'  => $item['detail_info']['navi_location']['lat']??'',
                ];
            }
        }
        $return['total'] = count($data);
        return self::createJsonData(true,$return);
    }

    //企业信息
    public function dataInfo(Request $request){
        $company = CompanyData::findOrFail($request->input('id'));
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $distance = '未知';
        if ($longitude && $company->longitude) {
            $distance = getDistanceByLatLng($company->longitude,$company->latitude,$longitude,$latitude);
            $distance = bcadd($distance,0,0);
        }
        //$tags = $company->tags()->pluck('name')->toArray();
        $return = [
            'id' => $company->id,
            'name' => $company->name,
            'logo' => $company->logo,
            'address_province' => $company->address_province,
            'address_detail' => $company->address_detail,
            'tags' => [],
            'distance' => $distance,
            'distance_format' => distanceFormat($distance)
        ];
        return self::createJsonData(true,$return);
    }

    //企业相关人员
    public function dataPeople(Request $request){
        $loginUser = $request->user();
        $companyUsers = CompanyDataUser::where('audit_status',1)->where('company_data_id',$request->input('id'))->simplePaginate(Config::get('inwehub.api_data_page_size'));
        $return = $companyUsers->toArray();
        $return['data'] = [];
        foreach ($companyUsers as $user) {
            $attention = Attention::where("user_id",'=',$loginUser->id)->where('source_type','=',get_class($user->user))->where('source_id','=',$user->user_id)->first();
            $is_followed = 0;
            if ($attention){
                $is_followed = 1;
            }
            $return['data'][] = [
                'id' => $user->user_id,
                'uuid' => $user->user->uuid,
                'name' => $user->user->name,
                'description' => $user->user->description,
                'avatar' => $user->user->avatar,
                'level' => $user->user->userData->user_level,
                'is_followed' => $is_followed,
                'is_expert' => $user->user->is_expert,
                'status_info' => $user->statusInfo()
            ];
        }
        return self::createJsonData(true,$return);
    }

    public function dataProduct(Request $request) {
        $validateRules = [
            'id' => 'required'
        ];
        $this->validate($request,$validateRules);
        $company = CompanyData::find($request->input('id'));
        $tags = $company->tags;
        $return = [];
        $return['current_page'] = 1;
        $return['from'] = null;
        $return['to'] = null;
        $return['per_page'] = 15;
        $return['next_page_url'] = null;
        $return['data'] = [];
        if ($request->input('page',1)>1) {
            return self::createJsonData(true,$return);
        }
        foreach ($tags as $tag) {
            $rel = TagCategoryRel::where('tag_id',$tag->id)->where('type',TagCategoryRel::TYPE_REVIEW)->where('status',1)->first();
            if ($rel) {
                $info = Tag::getReviewInfo($tag->id);
                $return['data'][] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'logo' => $tag->logo,
                    'review_count' => $info['review_count'],
                    'review_average_rate' => $info['review_average_rate']
                ];
            }
        }
        $return['current_page'] = 1;
        $return['from'] = 1;
        $return['to'] = count($return['data']);
        $return['per_page'] = count($return['data'])+1;
        $return['next_page_url'] = null;

        return self::createJsonData(true,$return);
    }

    //申请企业相关人员
    public function applyDataPeople(Request $request){
        $this->validate($request, [
            'id'          => 'required'
        ]);
        $user = $request->user();
        $company = CompanyData::findOrFail($request->input('id'));
        $fields = [];
        $fields[] = [
            'title'=>'公司名称',
            'value'=>$company->name
        ];
        $fields[] = [
            'title'=>'公司id',
            'value'=>$company->id
        ];
        $exist = CompanyDataUser::where('company_data_id',$company->id)->where('user_id',$user->id)->first();
        if (!$exist) {
            CompanyDataUser::create([
                'company_data_id' => $company->id,
                'user_id'         => $user->id,
                'audit_status'    => 0,
                'status'          => 1
            ]);
        }
        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.'申请了企业成员',$fields));
        return self::createJsonData(true,['tips'=>'申请成功，请耐心等待']);
    }

    //申请添加企业
    public function applyAddData(Request $request){
        $this->validate($request, [
            'name'          => 'required|min:2'
        ]);
        $user = $request->user();
        $fields = [];
        $fields[] = [
            'title'=>'公司名称',
            'value'=>$request->input('name')
        ];

        event(new SystemNotify('用户'.$user->id.'['.$user->name.']'.'申请添加企业',$fields));
        return self::createJsonData(true,['tips'=>'申请成功，请耐心等待']);
    }
}