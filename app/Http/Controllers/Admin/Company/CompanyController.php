<?php

namespace App\Http\Controllers\Admin\Company;

use App\Events\Frontend\System\Credit;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Area;
use App\Models\Authentication;
use App\Models\Company\Company;
use App\Models\Tag;
use App\Models\UserTag;
use App\Services\City\CityData;
use Illuminate\Http\Request;

use App\Http\Requests;

class CompanyController extends AdminController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Company::query();
        $filter =  $request->all();

        /*认证申请状态过滤*/
        if(isset($filter['apply_status']) && $filter['apply_status'] > -1){
            $query->where('apply_status','=',$filter['apply_status']);
        }

        if(isset($filter['user_id']) && $filter['user_id'] > 0 ){
            $query->where('user_id','=',$filter['user_id']);
        }

        $companies = $query->orderBy('updated_at','desc')->paginate(20);
        return view('admin.company.index')->with(compact('filter','companies'));
    }

    public function destroy(Request $request)
    {
        Company::whereIn('id',$request->input('id'))->update(['apply_status'=>Company::APPLY_STATUS_REJECT]);
        return $this->success(route('admin.company.index'),'审核不通过成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        Company::whereIn('id',$ids)->update(['apply_status'=>Company::APPLY_STATUS_SUCCESS]);

        return $this->success(route('admin.company.index'),'审核成功');

    }

}
