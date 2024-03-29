<?php

namespace App\Http\Controllers\Admin\Company;

use App\Events\Frontend\System\Credit;
use App\Http\Controllers\Admin\AdminController;
use App\Models\Company\Company;
use App\Models\User;
use App\Notifications\CompanyAuth;
use Illuminate\Http\Request;
use App\Models\Credit as CreditModel;

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
        $ids = $request->input('id');
        foreach ($ids as $id) {
            $company = Company::find($id);
            $company->apply_status = Company::APPLY_STATUS_REJECT;
            $company->save();
            $user = User::find($id);
            $user->notify(new CompanyAuth(Company::find($id)));
        }
        return $this->success(route('admin.company.index'),'审核不通过成功');
    }

    /*审核*/
    public function verify(Request $request)
    {
        $ids = $request->input('id');
        $action = CreditModel::KEY_COMPANY_VALID;
        foreach ($ids as $id) {
            $company = Company::find($id);
            $company->apply_status = Company::APPLY_STATUS_SUCCESS;
            $company->save();
            $user = User::find($id);
            event(new Credit($id,$action,Setting()->get('coins_'.$action),Setting()->get('credits_'.$action),$id,'企业认证'));

            $user->notify(new CompanyAuth(Company::find($id)));
        }

        return $this->success(route('admin.company.index'),'审核成功');

    }

}
